<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use App\Models\PurchaseOrderLine;
use App\Models\OpeningStock;
use App\Models\AuditLog;
use App\Models\StockMove;
use App\Services\SalesCrmService;
use App\Services\InventoryService;
use App\Services\PurchaseService;
use App\Services\AccountingService;

/**
 * RunM2W3CheckpointTests — M2-W3-01/02/03
 *
 * Cross-module integration tests for Week 3:
 *
 *  T-19 : Customer Invoice after Delivery Done (SR-02, F-09)
 *  T-26 : Idempotent Invoice posting — Post twice = 1 journal (SR-16)
 *  T-20 : SR-01 vs SR-02 conflict resolution (invoice qty cap)
 *  T-21 : Vendor Bill from GR — 3-way match (SR-06)
 *  T-22 : Vendor Bill > accepted_qty rejected (SR-06)
 *  T-25 : COGS journal after Delivery (F-14)
 *  T-31 : Return & Credit Note (SR-13)
 *  T-32 : Credit Note qty ceiling exceeded → rejected (SR-13)
 *  T-33 : Dashboard KPI from seed baseline (SR-19)
 *  T-34 : Draft invoice does NOT inflate AR (SR-19)
 *
 * Fixture chain: seed → T-09(SO) → T-11(Delivery Done) → T-17(GR Done) → T-19+ …
 *
 * Usage: php artisan m2:w3-checkpoint
 */
class RunM2W3CheckpointTests extends Command
{
    protected $signature   = 'm2:w3-checkpoint';
    protected $description = 'Run M2-W3 cross-module integration tests (T-19,T-20,T-21,T-22,T-25,T-26,T-31,T-32,T-33,T-34)';

    private int  $pass = 0;
    private int  $fail = 0;

    // IDs tracked across tests
    private string $soId       = '';
    private string $doId       = '';
    private string $grId       = '';
    private int    $invoiceId  = 0;
    private int    $billId     = 0;

    private SalesCrmService  $sales;
    private InventoryService $inventory;
    private PurchaseService  $purchase;
    private AccountingService $accounting;

    public function handle(): int
    {
        $this->sales      = app(SalesCrmService::class);
        $this->inventory  = app(InventoryService::class);
        $this->purchase   = app(PurchaseService::class);
        $this->accounting = app(AccountingService::class);

        $this->banner();

        // ── Fixture setup ──────────────────────────────────────────────────
        $this->line('  Setting up cross-module fixture (SO + Delivery + GR)...');
        $this->setupFixture();
        $this->line("  Fixture: SO={$this->soId}, DO={$this->doId}, GR={$this->grId}");
        $this->line('');

        // ── Tests ──────────────────────────────────────────────────────────
        $this->runTest('T-33', 'Dashboard KPI baseline dari seed (SR-19)',      fn() => $this->testT33());
        $this->runTest('T-34', 'Draft invoice tidak inflate AR (SR-19)',         fn() => $this->testT34());
        $this->runTest('T-25', 'COGS journal Delivery 10×P01 (F-14)',            fn() => $this->testT25());
        $this->runTest('T-19', 'Customer Invoice setelah Delivery Done (SR-02)', fn() => $this->testT19());
        $this->runTest('T-26', 'Post Invoice dua kali = 1 journal (SR-16)',      fn() => $this->testT26());
        $this->runTest('T-20', 'Invoice qty cap: delivered-invoiced (SR-02)',    fn() => $this->testT20());
        $this->runTest('T-21', 'Vendor Bill dari GR — 3-way match (SR-06)',      fn() => $this->testT21());
        $this->runTest('T-22', 'Bill > accepted_qty ditolak (SR-06)',            fn() => $this->testT22());
        $this->runTest('T-31', 'Return & Credit Note 2×P01 (SR-13)',            fn() => $this->testT31());
        $this->runTest('T-32', 'Credit Note qty melebihi ceiling → ditolak',    fn() => $this->testT32());

        $this->summary();
        return $this->fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  FIXTURE SETUP
    // ══════════════════════════════════════════════════════════════════════

    private function setupFixture(): void
    {
        // T-09 fixture: SO for C01, 10×P01
        $p01 = \App\Models\Product::find('P01');
        $so  = $this->sales->createQuotation([
            'customer_id'   => 'C01',
            'created_by'    => 'sales_user',
            'payment_terms' => 'NET14',
            'lines'         => [[
                'product_id'      => 'P01',
                'variant_sku'     => $p01->variant_sku,
                'product_name'    => $p01->name,
                'unit'            => $p01->unit,
                'quantity'        => 10,
                'unit_price'      => 25000,
                'discount_percent'=> 0,
                'source_location' => 'WH-JKT',
            ]],
        ]);
        $this->sales->confirmSalesOrder($so->id, 'sales_user');
        $this->soId = $so->id;

        // T-11 fixture: Delivery Done
        $delivery = $this->inventory->createDelivery($so->id, 'warehouse_mgr');
        $this->inventory->processDelivery($delivery->id, 'E01', 'warehouse_mgr');
        $this->doId = $delivery->id;

        // T-17 fixture: GR from PO00007, 20 received, 15 accepted
        $receipt = $this->inventory->createReceipt('PO00007', 'warehouse_mgr');
        $line = $receipt->lines->first();
        $this->inventory->processReceipt(
            $receipt->id, 'E01', 'warehouse_mgr',
            [$line->id => ['received_qty' => 20, 'accepted_qty' => 15]]
        );
        $this->grId = $receipt->id;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  T-33: Dashboard KPI baseline
    // ══════════════════════════════════════════════════════════════════════
    private function testT33(): void
    {
        // Stock
        $totalOnHand = (int) \App\Models\OpeningStock::sum('company_total');
        $stockValue  = (int) \App\Models\OpeningStock::sum('standard_value');

        // AR and AP from Journal Entries only (SR-19 — Posted documents only)
        $ar = (int)(JournalEntry::where('debit_account',  '1100')->sum('amount')
                  - JournalEntry::where('credit_account', '1100')->sum('amount'));
        $ap = (int)(JournalEntry::where('credit_account', '2000')->sum('amount')
                  - JournalEntry::where('debit_account',  '2000')->sum('amount'));
        $bank = (int)(JournalEntry::where('debit_account',  '1000')->sum('amount')
                    - JournalEntry::where('credit_account', '1000')->sum('amount'));

        // After fixture: T-09 SO confirmed, T-11 Delivery Done, T-17 GR Done
        // Opening: stock=880, but delivery reduced P01 by 10 → now <880
        $this->assert("Stock On Hand > 0",  $totalOnHand > 0, true, 'boolean');
        $this->assert("Stock Value > 0",    $stockValue > 0, true, 'boolean');
        $this->assert("Bank = 20.000.000 (opening, no payments yet)", $bank, 20_000_000);
        $this->assert("AP = 700.000 (opening — no bills posted yet)", $ap, 700_000);
        $this->assert("AR = 1.500.000 (opening — no invoices posted yet)", $ar, 1_500_000);

        // KPI dari dokumen confirmed (not draft)
        $openSOs = SalesOrder::whereIn('status', ['confirmed', 'done'])->count();
        $this->assert("Open SOs (Confirmed) >= 1", $openSOs >= 1, true, 'boolean');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  T-34: Draft invoice must NOT inflate AR (SR-19)
    // ══════════════════════════════════════════════════════════════════════
    private function testT34(): void
    {
        $so = SalesOrder::with('lines')->find($this->soId);
        $soLine = $so->lines->first();

        // Create a DRAFT invoice (do not post)
        $draftInvoice = $this->accounting->createCustomerInvoice([
            'partner_id'    => 'C01',
            'partner_name'  => 'PT Toko Makmur Sejahtera',
            'source_so'     => $this->soId,
            'payment_terms' => 'NET14',
            'created_by'    => 'finance_user',
            'lines'         => [[
                'product_id'    => 'P01',
                'variant_sku'   => $soLine->variant_sku,
                'product_name'  => $soLine->product_name,
                'unit'          => $soLine->unit,
                'unit_price'    => (int)$soLine->unit_price,
                'delivered_qty' => (int)$soLine->delivered_qty,
                'invoiced_qty'  => (int)$soLine->invoiced_qty,
            ]],
        ]);

        $this->assert("Draft invoice created (status=draft)", $draftInvoice->status, 'draft');

        // AR must NOT change from draft
        $arBefore = (int)(JournalEntry::where('debit_account','1100')->sum('amount')
                       - JournalEntry::where('credit_account','1100')->sum('amount'));
        $this->assert("AR unchanged after Draft Invoice (SR-19)", $arBefore, 1_500_000);

        // No journal entries for this draft invoice
        $journalCount = JournalEntry::where('invoice_id', $draftInvoice->id)->count();
        $this->assert("Draft invoice: zero journal entries", $journalCount, 0);

        // Store for T-19
        $this->invoiceId = $draftInvoice->id;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  T-25: COGS journal for Delivery 10×P01
    // ══════════════════════════════════════════════════════════════════════
    private function testT25(): void
    {
        $cogs = JournalEntry::where('reference', $this->doId)
            ->where('debit_account',  '5100')
            ->where('credit_account', '1300')
            ->first();

        $this->assertNotNull("COGS journal exists (Dr5100/Cr1300)", $cogs);
        $this->assert("COGS amount = 170000 (10×17000)", (int)$cogs?->amount, 170_000);

        // Stock valuation should have decreased by COGS
        $p01Stock = OpeningStock::where('variant_sku', 'CLN-DTR-800-LMN')->first();
        $this->assert("P01 on_hand > 0 after delivery (SR-04)", (int)$p01Stock->wh_jkt_on_hand > 0, true, 'boolean');

        // StockMove record
        $move = StockMove::where('reference', $this->doId)->where('move_type', 'delivery')->first();
        $this->assertNotNull("StockMove delivery exists", $move);
        $this->assert("StockMove total_value = 170000", (int)$move?->total_value, 170_000);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  T-19: Customer Invoice from Delivery — post and verify AR
    // ══════════════════════════════════════════════════════════════════════
    private function testT19(): void
    {
        // Use draft invoice from T-34 (or create fresh)
        $invoice = $this->invoiceId ? Invoice::find($this->invoiceId) : null;
        if (!$invoice) {
            $so     = SalesOrder::with('lines')->find($this->soId);
            $soLine = $so->lines->first();
            $invoice = $this->accounting->createCustomerInvoice([
                'partner_id'    => 'C01',
                'partner_name'  => 'PT Toko Makmur Sejahtera',
                'source_so'     => $this->soId,
                'payment_terms' => 'NET14',
                'created_by'    => 'finance_user',
                'lines'         => [[
                    'product_id'    => 'P01',
                    'variant_sku'   => $soLine->variant_sku,
                    'product_name'  => $soLine->product_name,
                    'unit'          => $soLine->unit,
                    'unit_price'    => (int)$soLine->unit_price,
                    'delivered_qty' => (int)$soLine->delivered_qty,
                    'invoiced_qty'  => (int)$soLine->invoiced_qty,
                ]],
            ]);
            $this->invoiceId = $invoice->id;
        }

        $this->assert("Invoice status = draft", $invoice->status, 'draft');
        $this->assert("Invoice net = 250000",   (int)$invoice->untaxed_amount, 250_000);
        $this->assert("Invoice tax = 27500",    (int)$invoice->tax_amount,      27_500);
        $this->assert("Invoice gross = 277500", (int)$invoice->gross_amount,   277_500);

        // Post the invoice
        $posted = $this->accounting->postInvoice($invoice, 'finance_user');
        $this->assert("Invoice status = posted", $posted->status, 'posted');

        // AR should now reflect the invoice
        $ar = (int)(JournalEntry::where('debit_account','1100')->sum('amount')
                  - JournalEntry::where('credit_account','1100')->sum('amount'));
        // Opening AR = 1,500,000. Invoice net = 250k + tax = 27.5k → total AR entry = 277.5k
        // But journal posts AR side as net + tax as two separate entries
        $this->assert("AR = 1,500,000 + 277,500 = 1,777,500", $ar, 1_777_500);
        $this->assert("Bank unchanged = 20,000,000", (int)(JournalEntry::where('debit_account','1000')->sum('amount') - JournalEntry::where('credit_account','1000')->sum('amount')), 20_000_000);

        // invoiced_qty updated on SO line (M2-W3-01)
        $soLine = SalesOrderLine::where('sales_order_id', $this->soId)->first();
        $this->assert("SO line invoiced_qty updated to 10", (int)$soLine->invoiced_qty, 10);

        // Journal entries: Dr1100/Cr4000 + Dr1100/Cr2100
        $invoiceJournals = JournalEntry::where('invoice_id', $posted->id)->get();
        $this->assert("Invoice has 2 journal entries (net + tax)", $invoiceJournals->count(), 2);

        $arEntry  = $invoiceJournals->firstWhere('debit_account', '1100');
        $taxEntry = $invoiceJournals->firstWhere('credit_account', '2100');
        $this->assertNotNull("Dr AR journal exists",      $arEntry);
        $this->assertNotNull("Cr VAT Output journal exists", $taxEntry);
        $this->assert("AR entry amount = 250000",   (int)$arEntry?->amount, 250_000);
        $this->assert("VAT entry amount = 27500",   (int)$taxEntry?->amount, 27_500);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  T-26: Idempotent posting — Post same invoice twice (SR-16)
    // ══════════════════════════════════════════════════════════════════════
    private function testT26(): void
    {
        $invoice = Invoice::find($this->invoiceId);
        $this->assert("Invoice is already posted", $invoice?->status, 'posted');

        $threw  = false;
        $errMsg = '';
        try {
            $this->accounting->postInvoice($invoice, 'finance_user');
        } catch (\RuntimeException $e) {
            $threw  = true;
            $errMsg = $e->getMessage();
        }

        $this->assert("SR-16: Second post throws exception", $threw, true, 'boolean');
        $this->assert("Error mentions SR-16 or already posted",
            str_contains($errMsg, 'SR-16') || str_contains($errMsg, 'diposting') || str_contains($errMsg, 'sudah'), true, 'boolean');

        // Verify journal count unchanged — still exactly 2
        $journalCount = JournalEntry::where('invoice_id', $this->invoiceId)->count();
        $this->assert("Journal count still = 2 (no duplicate, SR-16)", $journalCount, 2);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  T-20: Invoice qty cap — SR-02 enforced
    // ══════════════════════════════════════════════════════════════════════
    private function testT20(): void
    {
        // invoiced_qty is now 10 (from T-19). Try to invoice again for same SO line.
        $so     = SalesOrder::with('lines')->find($this->soId);
        $soLine = $so->lines->first();

        $threw = false;
        try {
            $this->accounting->createCustomerInvoice([
                'partner_id'    => 'C01',
                'partner_name'  => 'PT Toko Makmur Sejahtera',
                'source_so'     => $this->soId,
                'payment_terms' => 'NET14',
                'created_by'    => 'finance_user',
                'lines'         => [[
                    'product_id'    => 'P01',
                    'variant_sku'   => $soLine->variant_sku,
                    'product_name'  => $soLine->product_name,
                    'unit'          => $soLine->unit,
                    'unit_price'    => (int)$soLine->unit_price,
                    'delivered_qty' => (int)$soLine->delivered_qty,   // still 10
                    'invoiced_qty'  => (int)$soLine->invoiced_qty,    // now 10
                ]],
            ]);
        } catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'SR-02') || str_contains($e->getMessage(), 'terkirim');
        }
        $this->assert("SR-02: Re-invoicing same qty throws (delivered=invoiced)", $threw, true, 'boolean');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  T-21: Vendor Bill from GR (15 accepted × P05 @ Rp32.000)
    // ══════════════════════════════════════════════════════════════════════
    private function testT21(): void
    {
        $poLine = \App\Models\PurchaseOrderLine::where('purchase_order_id', 'PO00007')->first();
        $this->assertNotNull("PO00007 line exists", $poLine);

        $alreadyBilled = (int)$poLine->billed_qty; // should be 0 before bill
        $acceptedQty   = (int)$poLine->accepted_qty; // 15 from T-17 fixture

        $bill = $this->accounting->createVendorBill([
            'partner_id'    => 'V04',
            'partner_name'  => 'PT Personal Sentosa',
            'source_po'     => 'PO00007',
            'payment_terms' => 'NET14',
            'created_by'    => 'finance_user',
            'lines'         => [[
                'product_id'     => 'P05',
                'variant_sku'    => 'PRS-SHM-B24-MNT',
                'product_name'   => 'Sampo Sachet Dus Mentol',
                'unit'           => 'dus',
                'unit_price'     => 32_000,
                'accepted_qty'   => $acceptedQty,
                'already_billed' => $alreadyBilled,
            ]],
        ]);

        $this->assert("Bill created (status=draft)", $bill->status, 'draft');
        $this->assert("Bill net = 480000 (15×32000)", (int)$bill->untaxed_amount, 480_000);
        $this->assert("Bill tax = 52800 (11% of 480k)", (int)$bill->tax_amount, 52_800);
        $this->assert("Bill gross = 532800", (int)$bill->gross_amount, 532_800);

        // Post the bill
        $postedBill = $this->accounting->postInvoice($bill, 'finance_user');
        $this->assert("Bill status = posted", $postedBill->status, 'posted');

        // AP should increase
        $ap = (int)(JournalEntry::where('credit_account','2000')->sum('amount')
                  - JournalEntry::where('debit_account','2000')->sum('amount'));
        $this->assert("AP = 700000 + 480000 + 52800 = 1,232,800", $ap, 1_232_800);

        // billed_qty updated on PO line (M2-W3-01)
        $poLine->refresh();
        $this->assert("PO line billed_qty updated to 15", (int)$poLine->billed_qty, 15);

        $this->billId = $bill->id;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  T-22: Bill > accepted_qty rejected (SR-06)
    // ══════════════════════════════════════════════════════════════════════
    private function testT22(): void
    {
        // After T-21, billed_qty=15, accepted_qty=15 → billable=0
        $poLine = \App\Models\PurchaseOrderLine::where('purchase_order_id', 'PO00007')->first();

        $threw = false;
        try {
            $this->accounting->createVendorBill([
                'partner_id'    => 'V04',
                'partner_name'  => 'PT Personal Sentosa',
                'source_po'     => 'PO00007',
                'payment_terms' => 'NET14',
                'created_by'    => 'finance_user',
                'lines'         => [[
                    'product_id'     => 'P05',
                    'variant_sku'    => 'PRS-SHM-B24-MNT',
                    'product_name'   => 'Sampo Sachet Dus Mentol',
                    'unit'           => 'dus',
                    'unit_price'     => 32_000,
                    'accepted_qty'   => (int)$poLine->accepted_qty,   // 15
                    'already_billed' => (int)$poLine->billed_qty,     // 15 → billable=0
                ]],
            ]);
        } catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'SR-06') || str_contains($e->getMessage(), 'accepted');
        }
        $this->assert("SR-06: Re-billing already-billed accepted_qty rejected", $threw, true, 'boolean');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  T-31: Return & Credit Note (2×P01 layak jual)
    // ══════════════════════════════════════════════════════════════════════
    private function testT31(): void
    {
        $invoice = Invoice::with('items')->find($this->invoiceId);
        $this->assert("Invoice is posted (T-31 fixture)", $invoice?->status, 'posted');

        $p01StockBefore = (int) OpeningStock::where('variant_sku', 'CLN-DTR-800-LMN')->value('wh_jkt_on_hand');
        $arBefore = (int)(JournalEntry::where('debit_account','1100')->sum('amount')
                       - JournalEntry::where('credit_account','1100')->sum('amount'));

        // Return 2×P01 layak jual → COGS reversal + Credit Note
        $cn = $this->accounting->createCreditNote($invoice, [
            [
                'variant_sku' => 'CLN-DTR-800-LMN',
                'qty'         => 2,
                'is_saleable' => true,
                'unit_cost'   => 17_000,
            ],
        ], 'finance_user');

        $this->assert("Credit Note created (status=draft)", $cn->status, 'draft');
        $this->assert("CN net = 50000 (2×25000)",  (int)$cn->untaxed_amount, 50_000);
        $this->assert("CN tax = 5500 (11% of 50k)",(int)$cn->tax_amount,      5_500);
        $this->assert("CN gross = 55500",           (int)$cn->gross_amount,   55_500);
        $this->assert("CN linked to original invoice", (int)$cn->original_invoice_id, $this->invoiceId);

        // COGS reversal journal created at CN creation time (is_saleable=true)
        $reversalJe = JournalEntry::where('reference', 'like', 'COGS-REVERSAL-%')
            ->where('debit_account', '1300')
            ->where('credit_account', '5100')
            ->orderByDesc('id')->first();
        $this->assertNotNull("COGS reversal journal created on CN (Dr1300/Cr5100)", $reversalJe);
        $this->assert("COGS reversal = 34000 (2×17000)", (int)$reversalJe?->amount, 34_000);

        // Post the Credit Note
        $postedCn = $this->accounting->postInvoice($cn, 'finance_user');
        $this->assert("CN status = posted", $postedCn->status, 'posted');

        // AR should decrease by CN gross
        $arAfter = (int)(JournalEntry::where('debit_account','1100')->sum('amount')
                      - JournalEntry::where('credit_account','1100')->sum('amount'));
        // CN posts Dr4100 / Cr1100 (net) + Dr2100 / Cr1100 (tax reversal)
        $this->assert("AR reduced by 55500 after CN posted",
            $arAfter, $arBefore - 55_500);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  T-32: Credit Note qty ceiling — cannot exceed invoiced qty (SR-13)
    // ══════════════════════════════════════════════════════════════════════
    private function testT32(): void
    {
        $invoice = Invoice::with('items')->find($this->invoiceId);

        $threw = false;
        try {
            // Try to credit 11 units when only 10 were invoiced (2 already credited)
            $this->accounting->createCreditNote($invoice, [
                ['variant_sku' => 'CLN-DTR-800-LMN', 'qty' => 11, 'is_saleable' => false, 'unit_cost' => 17_000],
            ], 'finance_user');
        } catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'SR-13') || str_contains($e->getMessage(), 'melebihi');
        }
        $this->assert("SR-13: CN qty 11 > max creditable → rejected", $threw, true, 'boolean');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  HELPERS
    // ══════════════════════════════════════════════════════════════════════

    private function runTest(string $id, string $name, callable $fn): void
    {
        $this->line("  -- {$id}: {$name}");
        $before = $this->fail;
        try {
            $fn();
        } catch (\Throwable $e) {
            $this->fail++;
            $this->line("     EXCEPTION: " . $e->getMessage());
        }
        $result = $this->fail === $before ? '✔  PASSED' : '✘  FAILED';
        $this->line("  {$result}: {$id}");
        $this->line('');
    }

    private function assert(string $label, mixed $actual, mixed $expected, string $type = 'value'): void
    {
        $pass = match($type) {
            'boolean' => (bool)$actual === (bool)$expected,
            default   => $actual === $expected,
        };
        if ($pass) {
            $this->pass++;
            $this->line("     PASS  {$label} -> " . $this->fmt($actual));
        } else {
            $this->fail++;
            $this->line("     FAIL  {$label}");
            $this->line("           expected: " . $this->fmt($expected));
            $this->line("           actual:   " . $this->fmt($actual));
        }
    }

    private function assertNotNull(string $label, mixed $value): void
    {
        if ($value !== null) { $this->pass++; $this->line("     PASS  {$label}"); }
        else { $this->fail++; $this->line("     FAIL  {$label} -> NULL"); }
    }

    private function fmt(mixed $v): string
    {
        if (is_bool($v)) return $v ? 'true' : 'false';
        if (is_null($v)) return 'NULL';
        if (is_int($v) || is_float($v)) return number_format((float)$v);
        return (string)$v;
    }

    private function banner(): void
    {
        $this->line('');
        $this->line('  ╔══════════════════════════════════════════════════╗');
        $this->line('  ║  M2-W3  Cross-Module Integration Tests           ║');
        $this->line('  ║  PT Arunika ERP — Accounting & Invoicing         ║');
        $this->line('  ╚══════════════════════════════════════════════════╝');
        $this->line('');
    }

    private function summary(): void
    {
        $total = $this->pass + $this->fail;
        $this->line('  ════════════════════════════════════════════════════');
        if ($this->fail === 0) {
            $this->line("  ALL TESTS PASSED - {$this->pass}/{$total} assertions");
            $this->line('  M2-W3 Week 3 integration complete.');
        } else {
            $this->line("  {$this->fail} FAILURE(S) - {$this->pass}/{$total} assertions passed");
        }
        $this->line('  ════════════════════════════════════════════════════');
        $this->line('');
    }
}
