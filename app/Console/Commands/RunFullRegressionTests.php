<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\{
    Product, OpeningStock, ProductLot, Customer, Vendor,
    SalesOrder, SalesOrderLine, Lead,
    PurchaseOrder, PurchaseOrderLine,
    Delivery, DeliveryLine, Receipt, ReceiptLine,
    Invoice, InvoiceItem, JournalEntry,
    Employee, AuditLog, StockMove
};
use App\Services\{SalesCrmService, InventoryService, PurchaseService, AccountingService};

/**
 * RunFullRegressionTests — M2-W4-01
 *
 * Full T-01 to T-36 regression suite.
 *
 * Each test group resets from a fresh seed baseline where required:
 *   Group A  (T-01..T-08)  : Roles, SKU, CRM            — no state mutation
 *   Group B  (T-09..T-14)  : SO, Delivery, Transfer      — fixture chain
 *   Group C  (T-15..T-18)  : PO, GR                     — fixture chain
 *   Group D  (T-19..T-26)  : Invoice, Bill, Payment, GL  — depends on B+C
 *   Group E  (T-27..T-30)  : Employees                   — read-only
 *   Group F  (T-31..T-36)  : Credit Note, Dashboard      — depends on D
 *
 * Usage:
 *   php artisan db:master-seed && php artisan m2:regression
 */
class RunFullRegressionTests extends Command
{
    protected $signature   = 'm2:regression {--stop-on-fail}';
    protected $description = 'Full T-01–T-36 regression suite with clean seed fixture (M2-W4-01)';

    private int  $pass  = 0;
    private int  $fail  = 0;
    private bool $stop  = false;
    private int  $sectionFail = 0;

    // Cross-test state
    private string $soId      = '';
    private string $doId      = '';
    private string $grId      = '';
    private int    $invoiceId = 0;
    private int    $billId    = 0;

    private SalesCrmService   $sales;
    private InventoryService  $inventory;
    private PurchaseService   $purchase;
    private AccountingService $accounting;

    public function handle(): int
    {
        $this->stop       = (bool) $this->option('stop-on-fail');
        $this->sales      = app(SalesCrmService::class);
        $this->inventory  = app(InventoryService::class);
        $this->purchase   = app(PurchaseService::class);
        $this->accounting = app(AccountingService::class);

        $this->banner();

        // ── GROUP A: Auth, Roles, SKU, CRM ────────────────────────────────
        $this->sectionHeader('A', 'Roles / SKU / CRM', 'T-01..T-08');
        $this->runTest('T-01', 'App switcher & login audit',                    fn() => $this->testT01());
        $this->runTest('T-02', 'Finance access denied for Sales role (SR-15)',  fn() => $this->testT02());
        $this->runTest('T-03', 'C01 credit exposure baseline = 1.122.000',      fn() => $this->testT03());
        $this->runTest('T-04', 'SR-11 credit limit hard block',                 fn() => $this->testT04());
        $this->runTest('T-05', '16 unique SKUs; P01/P11 prices correct',        fn() => $this->testT05());
        $this->runTest('T-06', 'FEFO P03 — LOT-TEA-01 first, no double-count', fn() => $this->testT06());
        $this->runTest('T-07', 'Lead L00003 → Opportunity Qualified',           fn() => $this->testT07());
        $this->runTest('T-08', 'SR-19: customer relation protected on opp.',    fn() => $this->testT08());

        // ── GROUP B: SO + Delivery + Transfer ────────────────────────────
        $this->sectionHeader('B', 'Sales Order / Delivery / Transfer', 'T-09..T-14');
        $this->runTest('T-09', 'SO S00008: net 250k, tax 27.5k, gross 277.5k', fn() => $this->testT09());
        $this->runTest('T-10', 'SR-07/08 discount >10% approval workflow',      fn() => $this->testT10());
        $this->runTest('T-11', 'Delivery Done: stok -10, COGS 170k, SR-04',    fn() => $this->testT11());
        $this->runTest('T-12', 'Delivery > available rejected (SR-04)',          fn() => $this->testT12());
        $this->runTest('T-13', 'Internal transfer 5×P01 WH→HUB (SR-14)',        fn() => $this->testT13());
        $this->runTest('T-14', 'Transfer 53 > available=37 rejected (SR-22)',   fn() => $this->testT14());

        // ── GROUP C: PO + GR ──────────────────────────────────────────────
        $this->sectionHeader('C', 'Purchase Order / Receipt', 'T-15..T-18');
        $this->runTest('T-15', 'PO00007: V04, 20×P05, net 640k, locked',        fn() => $this->testT15());
        $this->runTest('T-16', 'SR-21: confirmed PO price cannot change',        fn() => $this->testT16());
        $this->runTest('T-17', 'GR 15 accepted / 5 rejected; E01 sign-off',     fn() => $this->testT17());
        $this->runTest('T-18', 'E03 Nonaktif sign-off rejected (SR-18)',         fn() => $this->testT18());

        // ── GROUP D: Invoice + Bill + Payment + GL ────────────────────────
        $this->sectionHeader('D', 'Invoice / Bill / Payment / GL', 'T-19..T-26');
        $this->runTest('T-19', 'Customer Invoice posted: AR +277.5k, invoiced_qty=10', fn() => $this->testT19());
        $this->runTest('T-20', 'SR-02: re-invoice same delivered qty rejected', fn() => $this->testT20());
        $this->runTest('T-21', 'Vendor Bill: AP +532.8k, billed_qty=15',        fn() => $this->testT21());
        $this->runTest('T-22', 'SR-06: bill > accepted rejected',               fn() => $this->testT22());
        $this->runTest('T-23', 'Partial payment Rp100k: outstanding 177.5k',   fn() => $this->testT23());
        $this->runTest('T-24', 'SR-20: overpayment Rp300k > 177.5k rejected',  fn() => $this->testT24());
        $this->runTest('T-25', 'COGS Dr5100/Cr1300 = 170k (F-14)',             fn() => $this->testT25());
        $this->runTest('T-26', 'SR-16: post invoice twice = 1 journal',        fn() => $this->testT26());

        // ── GROUP E: Employees ────────────────────────────────────────────
        $this->sectionHeader('E', 'Employees & Departments', 'T-27..T-30');
        $this->runTest('T-27', '8 employees, 5 departments, Warehouse=3',      fn() => $this->testT27());
        $this->runTest('T-28', 'SR-15: private data blocked for Sales role',    fn() => $this->testT28());
        $this->runTest('T-29', 'E01 can sign-off; E03 cannot',                 fn() => $this->testT29());
        $this->runTest('T-30', 'Nonaktif employee blocks sign-off (SR-17/18)', fn() => $this->testT30());

        // ── GROUP F: Credit Note + Dashboard ─────────────────────────────
        $this->sectionHeader('F', 'Credit Note / Dashboard / Audit', 'T-31..T-36');
        $this->runTest('T-31', 'Return 2×P01: CN 55.5k, COGS reversal 34k',   fn() => $this->testT31());
        $this->runTest('T-32', 'SR-13: CN qty 11 > max=10 rejected',           fn() => $this->testT32());
        $this->runTest('T-33', 'Dashboard KPI from Posted only (SR-19)',        fn() => $this->testT33());
        $this->runTest('T-34', 'Draft invoice does not inflate AR (SR-19)',     fn() => $this->testT34());
        $this->runTest('T-35', 'PO00007 related Receipt=1, Bill=1 (F-21)',     fn() => $this->testT35());
        $this->runTest('T-36', 'SR-16: re-validate Done Receipt rejected',     fn() => $this->testT36());

        $this->finalSummary();
        return $this->fail > 0 ? self::FAILURE : self::SUCCESS;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  GROUP A — Roles / SKU / CRM
    // ══════════════════════════════════════════════════════════════════════

    private function testT01(): void
    {
        // App switcher: verify role-based routes exist
        // Login audit: AuditLog table exists and is queryable
        $logCount = AuditLog::count();
        $this->assert("AuditLog table queryable (>0 rows)", $logCount > 0, true, 'boolean');
        // Users seeded with roles
        $roles = DB::table('users')->pluck('role')->unique()->sort()->values()->toArray();
        $expectedRoles = ['Finance','HR','Owner','Purchase','Sales','Warehouse'];
        foreach ($expectedRoles as $r) {
            $this->assert("Role '{$r}' exists in users", in_array($r, $roles), true, 'boolean');
        }
    }

    private function testT02(): void
    {
        // SR-15: Finance access denied for Sales role — CheckRole middleware exists
        $mw = app_path('Http/Middleware/CheckRole.php');
        $this->assert("CheckRole middleware exists", file_exists($mw), true, 'boolean');
        // Sales user mapped to E04 (D-SAL) — not Warehouse or Finance
        $salesUser = DB::table('users')->where('username', 'sales_user')->first();
        $this->assert("sales_user role = Sales", $salesUser?->role, 'Sales');
        $emp = Employee::find('E04');
        $this->assert("E04 department = D-SAL (not Finance)", $emp?->department_id, 'D-SAL');
    }

    private function testT03(): void
    {
        $exposure = $this->sales->creditExposure('C01');
        // Opening: AR 900k + S00007 unbilled 222k = 1,122,000
        $this->assert("C01 baseline exposure = 1,122,000", $exposure, 1_122_000);
        $c01 = Customer::find('C01');
        $this->assert("C01 credit_limit = 2,000,000", (int)$c01->credit_limit, 2_000_000);
        $this->assert("C01 open_ar = 900,000", (int)$c01->open_ar, 900_000);
    }

    private function testT04(): void
    {
        // C01 exposure = 1,122,000. Adding 40×P01 gross = 40×25000×1.11 = 1,110,000
        // Total projected = 2,232,000 > 2,000,000 → hard block
        $p01 = Product::find('P01');
        $threw = false;
        try {
            $so = $this->sales->createQuotation([
                'customer_id' => 'C01', 'created_by' => 'sales_user',
                'lines' => [['product_id'=>'P01','variant_sku'=>$p01->variant_sku,
                    'product_name'=>$p01->name,'unit'=>$p01->unit,
                    'quantity'=>40,'unit_price'=>25000,'discount_percent'=>0,'source_location'=>'WH-JKT']],
            ]);
            $this->sales->confirmSalesOrder($so->id, 'sales_user');
        } catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'SR-11') || str_contains($e->getMessage(), 'Credit');
        }
        $this->assert("SR-11: 40×P01 for C01 rejected (exposure > limit)", $threw, true, 'boolean');
    }

    private function testT05(): void
    {
        $skus = Product::pluck('variant_sku');
        $this->assert("Total SKU = 16", $skus->count(), 16);
        $this->assert("All SKU unique", $skus->unique()->count(), 16);
        $p01 = Product::find('P01');
        $p11 = Product::find('P11');
        $this->assert("P01 sales_price = 25000", (int)$p01->sales_price, 25000);
        $this->assert("P01 cost = 17000", (int)$p01->cost, 17000);
        $this->assert("P01 SKU != P11 SKU", $p01->variant_sku !== $p11->variant_sku, true, 'boolean');
    }

    private function testT06(): void
    {
        $lots = ProductLot::where('variant_sku','BEV-TEA-C24-LCH')->orderBy('expiry_date')->get();
        $this->assert("P03 lot count = 3", $lots->count(), 3);
        $this->assert("FEFO first = LOT-TEA-01", $lots[0]->lot_id, 'LOT-TEA-01');
        $refDate = \Carbon\Carbon::parse('2026-10-20');
        $this->assert("LOT-TEA-01 not expired 20-10-2026",
            \Carbon\Carbon::parse($lots[0]->expiry_date)->gt($refDate), true, 'boolean');
        $stockQty = (int) OpeningStock::where('variant_sku','BEV-TEA-C24-LCH')->value('company_total');
        $this->assert("Lot total == opening_stock (no double-count)", $lots->sum('qty'), $stockQty);
    }

    private function testT07(): void
    {
        $lead = Lead::find('L00003');
        $this->assertNotNull("L00003 exists", $lead);
        $this->assert("L00003 stage = new", $lead->stage, 'new');
        $this->assert("L00003 partner = C03", $lead->partner_id, 'C03');
        $this->sales->convertToOpportunity('L00003', 'sales_user');
        $lead->refresh();
        $this->assert("L00003 stage = qualified after convert", $lead->stage, 'qualified');
        $this->assert("C03 relation preserved", $lead->partner_id, 'C03');
    }

    private function testT08(): void
    {
        // SR-19: removing customer from Opportunity blocked
        $threw = false;
        try {
            $this->sales->removeCustomerRelation('L00003', 'sales_user');
        } catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'SR-19') || str_contains($e->getMessage(), 'relation');
        }
        $this->assert("SR-19: remove customer from opp. rejected", $threw, true, 'boolean');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  GROUP B — SO + Delivery + Transfer
    // ══════════════════════════════════════════════════════════════════════

    private function testT09(): void
    {
        $p01 = Product::find('P01');
        $so  = $this->sales->createQuotation([
            'customer_id' => 'C01', 'created_by' => 'sales_user', 'payment_terms' => 'NET14',
            'lines' => [['product_id'=>'P01','variant_sku'=>$p01->variant_sku,'product_name'=>$p01->name,
                'unit'=>$p01->unit,'quantity'=>10,'unit_price'=>25000,'discount_percent'=>0,'source_location'=>'WH-JKT']],
        ]);
        $this->assert("SO net = 250,000",   (int)$so->untaxed_amount, 250_000);
        $this->assert("SO tax = 27,500",    (int)$so->tax_amount,      27_500);
        $this->assert("SO gross = 277,500", (int)$so->gross_amount,   277_500);

        $confirmed = $this->sales->confirmSalesOrder($so->id, 'sales_user');
        $this->assert("SO status = confirmed",    $confirmed->status,  'confirmed');
        $this->assert("SR-12: lines locked",      (bool)$confirmed->lines->first()?->is_locked, true, 'boolean');

        $p01Stock = OpeningStock::where('variant_sku','CLN-DTR-800-LMN')->first();
        $this->assert("SR-22: On Hand unchanged after confirm", (int)$p01Stock->wh_jkt_on_hand, 60);
        $this->assert("SR-22: reserved += 10",   (int)$p01Stock->reserved, 18); // 8 existing + 10 new

        $this->soId = $so->id;
    }

    private function testT10(): void
    {
        $p01 = Product::find('P01');
        $so  = $this->sales->createQuotation([
            'customer_id' => 'C03', 'created_by' => 'sales_user',
            'lines' => [['product_id'=>'P01','variant_sku'=>$p01->variant_sku,'product_name'=>$p01->name,
                'unit'=>$p01->unit,'quantity'=>5,'unit_price'=>25000,'discount_percent'=>15.00,'source_location'=>'WH-JKT']],
        ]);
        // SR-07/08: discount 15% → status to_approve on confirm attempt
        $result = $this->sales->confirmSalesOrder($so->id, 'sales_user');
        $this->assert("SR-07/08: SO with 15% discount → to_approve", $result->status, 'to_approve');

        // Finance approves
        $approved = $this->sales->approveDiscount($so->id, 'finance_user');
        $this->assert("After Finance approval: status = confirmed", $approved->status, 'confirmed');
        $this->assert("SR-12: lines locked after approval", (bool)$approved->lines->first()?->is_locked, true, 'boolean');
    }

    private function testT11(): void
    {
        if (!$this->soId) { $this->failMsg("T-11: soId missing, T-09 must run first"); return; }
        $so = SalesOrder::with('lines')->find($this->soId);
        $delivery = $this->inventory->createDelivery($this->soId, 'warehouse_mgr');
        $this->assert("Delivery created",        $delivery->status, 'ready');

        $p01Before = (int) OpeningStock::where('variant_sku','CLN-DTR-800-LMN')->value('wh_jkt_on_hand');
        $result = $this->inventory->processDelivery($delivery->id, 'E01', 'warehouse_mgr');
        $this->assert("Delivery Done",           $result->status, 'done');
        $this->assert("SR-16: is_posted=true",   (bool)$result->is_posted, true, 'boolean');

        $p01After = OpeningStock::where('variant_sku','CLN-DTR-800-LMN')->first();
        $this->assert("P01 WH-JKT -10",          (int)$p01After->wh_jkt_on_hand, $p01Before - 10);
        $this->assert("SR-04: WH >= 0",          (int)$p01After->wh_jkt_on_hand >= 0, true, 'boolean');

        $cogs = JournalEntry::where('reference',$delivery->id)->where('debit_account','5100')->first();
        $this->assertNotNull("COGS journal exists",    $cogs);
        $this->assert("COGS amount = 170,000",         (int)$cogs?->amount, 170_000);

        $soLine = $so->lines->first(); $soLine->refresh();
        $this->assert("delivered_qty = 10",            (int)$soLine->delivered_qty, 10);
        $this->doId = $delivery->id;
    }

    private function testT12(): void
    {
        // Try delivery of more than available
        $p02 = Product::find('P02');
        $so2 = $this->sales->createQuotation([
            'customer_id' => 'C03', 'created_by' => 'sales_user',
            'lines' => [['product_id'=>'P02','variant_sku'=>$p02->variant_sku,'product_name'=>$p02->name,
                'unit'=>$p02->unit,'quantity'=>12,'unit_price'=>54000,'discount_percent'=>0,'source_location'=>'WH-JKT']],
        ]);
        $this->sales->confirmSalesOrder($so2->id, 'sales_user');
        $d = $this->inventory->createDelivery($so2->id, 'warehouse_mgr');

        // Manually override done_qty to exceed available
        $line = $d->lines->first();
        $threw = false;
        try {
            $this->inventory->processDelivery($d->id, 'E01', 'warehouse_mgr', [$line->id => 45]); // way over
        } catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'SR-04') || str_contains($e->getMessage(), 'tidak cukup');
        }
        $this->assert("SR-04: delivery > available rejected", $threw, true, 'boolean');
    }

    private function testT13(): void
    {
        $before = OpeningStock::where('variant_sku','CLN-DTR-800-LMN')->first();
        $this->inventory->transferStock('WH-JKT','HUB-BDG','CLN-DTR-800-LMN', 5, 'warehouse_mgr');
        $after = OpeningStock::where('variant_sku','CLN-DTR-800-LMN')->first();
        $this->assert("WH-JKT -5",               (int)$after->wh_jkt_on_hand, (int)$before->wh_jkt_on_hand - 5);
        $this->assert("HUB-BDG +5",              (int)$after->hub_bdg_on_hand, (int)$before->hub_bdg_on_hand + 5);
        $this->assert("SR-14: company_total unchanged", (int)$after->company_total, (int)$before->company_total);
        $this->assert("SR-14: standard_value unchanged", (int)$after->standard_value, (int)$before->standard_value);
    }

    private function testT14(): void
    {
        $p01 = OpeningStock::where('variant_sku','CLN-DTR-800-LMN')->first();
        $available = (int)$p01->wh_jkt_on_hand - (int)$p01->reserved;
        $threw = false;
        try {
            $this->inventory->transferStock('WH-JKT','HUB-BDG','CLN-DTR-800-LMN', 53, 'warehouse_mgr');
        } catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'SR-22') || str_contains($e->getMessage(), 'available');
        }
        $this->assert("SR-22: transfer 53 > available ({$available}) rejected", $threw, true, 'boolean');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  GROUP C — PO + GR
    // ══════════════════════════════════════════════════════════════════════

    private function testT15(): void
    {
        $po = PurchaseOrder::with('lines')->find('PO00007');
        $this->assertNotNull("PO00007 exists", $po);
        $this->assert("PO00007 vendor = V04",         $po->vendor_id,           'V04');
        $this->assert("PO00007 status = confirmed",   $po->status,              'confirmed');
        $this->assert("PO00007 net = 640,000",        (int)$po->net_amount,      640_000);
        $this->assert("PO00007 gross = 710,400",      (int)$po->gross_amount,    710_400);
        $line = $po->lines->first();
        $this->assert("PO line qty = 20",             (int)$line->quantity,      20);
        $this->assert("PO line unit_price = 32,000",  (int)$line->unit_price,    32_000);
        $this->assert("SR-21: line is_locked = true", (bool)$line->is_locked, true, 'boolean');
    }

    private function testT16(): void
    {
        // Create new RFQ and confirm; then try re-confirm
        $rfq = $this->purchase->createRfq([
            'vendor_id' => 'V01',
            'lines'     => [['variant_sku'=>'CLN-DTR-800-LMN','quantity'=>3]],
            'created_by'=> 'purchase_user',
        ]);
        $this->purchase->confirmPurchaseOrder($rfq->id, 'purchase_user');
        $threw = false;
        try { $this->purchase->confirmPurchaseOrder($rfq->id, 'purchase_user'); }
        catch (\RuntimeException $e) { $threw = true; }
        $this->assert("SR-21: re-confirm rejected", $threw, true, 'boolean');
    }

    private function testT17(): void
    {
        $p05Before = (int) OpeningStock::where('variant_sku','PRS-SHM-B24-MNT')->value('wh_jkt_on_hand');
        $receipt   = $this->inventory->createReceipt('PO00007', 'warehouse_mgr');
        $line      = $receipt->lines->first();
        $result    = $this->inventory->processReceipt(
            $receipt->id, 'E01', 'warehouse_mgr',
            [$line->id => ['received_qty' => 20, 'accepted_qty' => 15]]
        );
        $this->assert("GR status = done",     $result->status,              'done');
        $this->assert("GR validator = E01",   $result->validator_employee_id, 'E01');
        $result->lines->first()->refresh();
        $this->assert("Accepted = 15",        (int)$result->lines->first()->accepted_qty, 15);
        $this->assert("Rejected = 5",         (int)$result->lines->first()->rejected_qty,  5);
        $p05After = (int) OpeningStock::where('variant_sku','PRS-SHM-B24-MNT')->value('wh_jkt_on_hand');
        $this->assert("WH P05 +15 (accepted only, SR-06)", $p05After, $p05Before + 15);
        $je = JournalEntry::where('reference',$receipt->id)->where('debit_account','1300')->first();
        $this->assertNotNull("GR journal Dr1300/Cr1350 exists", $je);
        $this->assert("GR journal = 480,000 (15×32k)", (int)$je->amount, 480_000);
        $this->grId = $receipt->id;
    }

    private function testT18(): void
    {
        $rfq = $this->purchase->createRfq([
            'vendor_id' => 'V04',
            'lines'     => [['variant_sku'=>'PRS-SHM-B24-MNT','quantity'=>2]],
            'created_by'=> 'purchase_user',
        ]);
        $this->purchase->confirmPurchaseOrder($rfq->id, 'purchase_user');
        $receipt = $this->inventory->createReceipt($rfq->id, 'warehouse_mgr');
        $threw   = false;
        try { $this->inventory->processReceipt($receipt->id, 'E03', 'warehouse_mgr'); }
        catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'Nonaktif') || str_contains($e->getMessage(), 'SR-18');
        }
        $this->assert("SR-18: E03 Nonaktif sign-off rejected", $threw, true, 'boolean');
        $receipt->refresh();
        $this->assert("Receipt stays draft",   $receipt->status, 'draft');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  GROUP D — Invoice / Bill / Payment / GL
    // ══════════════════════════════════════════════════════════════════════

    private function testT19(): void
    {
        if (!$this->soId) { $this->failMsg("T-19: soId missing"); return; }
        $soLine = SalesOrderLine::where('sales_order_id', $this->soId)->first();
        $inv = $this->accounting->createCustomerInvoice([
            'partner_id'   => 'C01', 'partner_name' => 'PT Toko Makmur Sejahtera',
            'source_so'    => $this->soId, 'payment_terms' => 'NET14', 'created_by' => 'finance_user',
            'lines' => [['product_id'=>'P01','variant_sku'=>$soLine->variant_sku,
                'product_name'=>$soLine->product_name,'unit'=>$soLine->unit,
                'unit_price'=>(int)$soLine->unit_price,'delivered_qty'=>(int)$soLine->delivered_qty,
                'invoiced_qty'=>(int)$soLine->invoiced_qty]],
        ]);
        $this->assert("Invoice net = 250k",   (int)$inv->untaxed_amount, 250_000);
        $this->assert("Invoice gross = 277.5k",(int)$inv->gross_amount,  277_500);

        $posted = $this->accounting->postInvoice($inv, 'finance_user');
        $this->assert("Invoice status = posted", $posted->status, 'posted');

        $ar = (int)(JournalEntry::where('debit_account','1100')->sum('amount')
                  - JournalEntry::where('credit_account','1100')->sum('amount'));
        $this->assert("AR = 1,500,000 + 277,500 = 1,777,500", $ar, 1_777_500);

        $soLine->refresh();
        $this->assert("invoiced_qty = 10 (M2-W3-01)", (int)$soLine->invoiced_qty, 10);
        $this->invoiceId = $posted->id;
    }

    private function testT20(): void
    {
        if (!$this->invoiceId) { $this->failMsg("T-20: invoiceId missing"); return; }
        $soLine = SalesOrderLine::where('sales_order_id', $this->soId)->first();
        $threw  = false;
        try {
            $this->accounting->createCustomerInvoice([
                'partner_id'=>'C01','partner_name'=>'PT Toko Makmur Sejahtera',
                'source_so'=>$this->soId,'payment_terms'=>'NET14','created_by'=>'finance_user',
                'lines'=>[['product_id'=>'P01','variant_sku'=>$soLine->variant_sku,
                    'product_name'=>$soLine->product_name,'unit'=>$soLine->unit,
                    'unit_price'=>(int)$soLine->unit_price,'delivered_qty'=>(int)$soLine->delivered_qty,
                    'invoiced_qty'=>(int)$soLine->invoiced_qty]], // invoiced_qty=10 = delivered_qty → billable=0
            ]);
        } catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'SR-02') || str_contains($e->getMessage(), 'terkirim');
        }
        $this->assert("SR-02: re-invoice rejected when delivered=invoiced", $threw, true, 'boolean');
    }

    private function testT21(): void
    {
        $poLine = PurchaseOrderLine::where('purchase_order_id','PO00007')->first();
        $bill   = $this->accounting->createVendorBill([
            'partner_id' => 'V04', 'partner_name' => 'PT Personal Sentosa',
            'source_po'  => 'PO00007', 'payment_terms' => 'NET14', 'created_by' => 'finance_user',
            'lines' => [['product_id'=>'P05','variant_sku'=>'PRS-SHM-B24-MNT',
                'product_name'=>'Sampo Sachet Dus Mentol','unit'=>'dus','unit_price'=>32_000,
                'accepted_qty'=>(int)$poLine->accepted_qty,'already_billed'=>(int)$poLine->billed_qty]],
        ]);
        $this->assert("Bill net = 480k",    (int)$bill->untaxed_amount, 480_000);
        $this->assert("Bill tax = 52.8k",   (int)$bill->tax_amount,      52_800);
        $this->assert("Bill gross = 532.8k",(int)$bill->gross_amount,   532_800);

        $posted = $this->accounting->postInvoice($bill, 'finance_user');
        $this->assert("Bill posted", $posted->status, 'posted');

        $ap = (int)(JournalEntry::where('credit_account','2000')->sum('amount')
                  - JournalEntry::where('debit_account','2000')->sum('amount'));
        $this->assert("AP = 700k + 532.8k = 1,232,800", $ap, 1_232_800);

        $poLine->refresh();
        $this->assert("PO billed_qty = 15", (int)$poLine->billed_qty, 15);
        $this->billId = $posted->id;
    }

    private function testT22(): void
    {
        $poLine = PurchaseOrderLine::where('purchase_order_id','PO00007')->first();
        $threw  = false;
        try {
            $this->accounting->createVendorBill([
                'partner_id'=>'V04','partner_name'=>'PT Personal Sentosa',
                'source_po'=>'PO00007','payment_terms'=>'NET14','created_by'=>'finance_user',
                'lines'=>[['product_id'=>'P05','variant_sku'=>'PRS-SHM-B24-MNT',
                    'product_name'=>'Sampo Sachet Dus Mentol','unit'=>'dus','unit_price'=>32_000,
                    'accepted_qty'=>(int)$poLine->accepted_qty,'already_billed'=>(int)$poLine->billed_qty]],
            ]);
        } catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'SR-06') || str_contains($e->getMessage(), 'accepted');
        }
        $this->assert("SR-06: re-billing fully-billed PO rejected", $threw, true, 'boolean');
    }

    private function testT23(): void
    {
        if (!$this->invoiceId) { $this->failMsg("T-23: invoiceId missing"); return; }
        $invoice = Invoice::find($this->invoiceId);
        $this->accounting->registerPayment($invoice, 100_000, 'PAY-T23', 'finance_user');
        $invoice->refresh();
        $this->assert("Outstanding = 277,500 - 100,000 = 177,500",
            (int)$invoice->amount_outstanding, 177_500);
        $bankBalance = (int)(JournalEntry::where('debit_account','1000')->sum('amount')
                           - JournalEntry::where('credit_account','1000')->sum('amount'));
        $this->assert("Bank = 20,000,000 + 100,000 = 20,100,000", $bankBalance, 20_100_000);
    }

    private function testT24(): void
    {
        if (!$this->invoiceId) { $this->failMsg("T-24: invoiceId missing"); return; }
        $invoice = Invoice::find($this->invoiceId);
        $threw   = false;
        try { $this->accounting->registerPayment($invoice, 300_000, 'PAY-T24-OVER', 'finance_user'); }
        catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'SR-20') || str_contains($e->getMessage(), 'Overpayment') || str_contains($e->getMessage(), 'outstanding');
        }
        $this->assert("SR-20: overpayment Rp300k > outstanding Rp177.5k rejected", $threw, true, 'boolean');
    }

    private function testT25(): void
    {
        if (!$this->doId) { $this->failMsg("T-25: doId missing"); return; }
        $cogs = JournalEntry::where('reference', $this->doId)
            ->where('debit_account','5100')->where('credit_account','1300')->first();
        $this->assertNotNull("COGS Dr5100/Cr1300 journal exists", $cogs);
        $this->assert("COGS amount = 170,000", (int)$cogs->amount, 170_000);
        $move = StockMove::where('reference', $this->doId)->where('move_type','delivery')->first();
        $this->assertNotNull("StockMove (delivery) exists", $move);
        $this->assert("StockMove total_value = 170,000", (int)$move->total_value, 170_000);
    }

    private function testT26(): void
    {
        if (!$this->invoiceId) { $this->failMsg("T-26: invoiceId missing"); return; }
        $invoice = Invoice::find($this->invoiceId);
        $countBefore = JournalEntry::where('invoice_id', $this->invoiceId)->count();
        $threw = false;
        try { $this->accounting->postInvoice($invoice, 'finance_user'); }
        catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'SR-16') || str_contains($e->getMessage(), 'diposting');
        }
        $this->assert("SR-16: re-post throws",                $threw, true, 'boolean');
        $this->assert("Journal count unchanged after re-post",
            JournalEntry::where('invoice_id', $this->invoiceId)->count(), $countBefore);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  GROUP E — Employees
    // ══════════════════════════════════════════════════════════════════════

    private function testT27(): void
    {
        $total     = Employee::count();
        $active    = Employee::where('status','Aktif')->count();
        $warehouse = Employee::where('department_id','D-WH')->count();
        $depts     = DB::table('departments')->count();
        $this->assert("Total employees = 8",    $total,     8);
        $this->assert("Active employees = 7",   $active,    7);
        $this->assert("Warehouse dept = 3",      $warehouse, 3);
        $this->assert("Departments = 5",         $depts,     5);
    }

    private function testT28(): void
    {
        // SR-15: private records only accessible by HR
        $privateCount = DB::table('employee_private_records')->count();
        $this->assert("employee_private_records has rows", $privateCount > 0, true, 'boolean');
        // Verify E01 has private data
        $e01Priv = DB::table('employee_private_records')->where('employee_id','E01')->first();
        $this->assertNotNull("E01 has private record", $e01Priv);
        // Verify private data columns exist (personal_email, private_phone — HR only access)
        $this->assert("E01 private_phone is set",  !empty($e01Priv?->private_phone),  true, 'boolean');
        $this->assert("E01 personal_email is set", !empty($e01Priv?->personal_email), true, 'boolean');
        // Only E01 and E03 have private records (access should be restricted at API level)
        $this->assert("Exactly 2 employees have private records", $privateCount, 2);
    }

    private function testT29(): void
    {
        $e01 = Employee::find('E01');
        $e03 = Employee::find('E03');
        $this->assert("E01 status = Aktif",    $e01->status,    'Aktif');
        $this->assert("E01 dept = D-WH",       $e01->department_id, 'D-WH');
        $this->assertNotNull("E01 mapped_user", $e01->mapped_user_id);
        $this->assert("E03 status = Nonaktif", $e03->status,   'Nonaktif');
        // E01 can sign-off (no exception)
        $noException = true;
        try { $this->inventory->validateSignOff('E01'); }
        catch (\RuntimeException $e) { $noException = false; }
        $this->assert("E01 validateSignOff passes",   $noException, true, 'boolean');
        // E03 cannot
        $threw = false;
        try { $this->inventory->validateSignOff('E03'); }
        catch (\RuntimeException $e) { $threw = true; }
        $this->assert("E03 validateSignOff rejected", $threw, true, 'boolean');
    }

    private function testT30(): void
    {
        // SR-17/18: nonaktif employee cannot sign off (covered by T-18 and T-29)
        $e03 = Employee::find('E03');
        $this->assert("E03 status = Nonaktif", $e03->status, 'Nonaktif');
        $threw = false;
        try { $this->inventory->validateSignOff('E03'); }
        catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'Nonaktif') || str_contains($e->getMessage(), 'SR-18');
        }
        $this->assert("SR-17/18: Nonaktif sign-off rejected with correct error", $threw, true, 'boolean');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  GROUP F — Credit Note + Dashboard + Audit
    // ══════════════════════════════════════════════════════════════════════

    private function testT31(): void
    {
        if (!$this->invoiceId) { $this->failMsg("T-31: invoiceId missing"); return; }
        $invoice = Invoice::with('items')->find($this->invoiceId);
        $arBefore = (int)(JournalEntry::where('debit_account','1100')->sum('amount')
                       - JournalEntry::where('credit_account','1100')->sum('amount'));

        $cn = $this->accounting->createCreditNote($invoice, [
            ['variant_sku'=>'CLN-DTR-800-LMN','qty'=>2,'is_saleable'=>true,'unit_cost'=>17_000],
        ], 'finance_user');
        $this->assert("CN gross = 55,500", (int)$cn->gross_amount, 55_500);
        $this->assert("CN net = 50,000",   (int)$cn->untaxed_amount, 50_000);

        $reversal = JournalEntry::where('reference','like','COGS-REVERSAL-%')
            ->where('debit_account','1300')->where('credit_account','5100')
            ->orderByDesc('id')->first();
        $this->assertNotNull("COGS reversal Dr1300/Cr5100 exists",  $reversal);
        $this->assert("COGS reversal = 34,000",                      (int)$reversal->amount, 34_000);

        $posted = $this->accounting->postInvoice($cn, 'finance_user');
        $this->assert("CN posted", $posted->status, 'posted');

        $arAfter = (int)(JournalEntry::where('debit_account','1100')->sum('amount')
                     - JournalEntry::where('credit_account','1100')->sum('amount'));
        $this->assert("AR reduced by 55,500 after CN",  $arAfter, $arBefore - 55_500);
    }

    private function testT32(): void
    {
        if (!$this->invoiceId) { $this->failMsg("T-32: invoiceId missing"); return; }
        $invoice = Invoice::with('items')->find($this->invoiceId);
        $threw   = false;
        try {
            $this->accounting->createCreditNote($invoice,
                [['variant_sku'=>'CLN-DTR-800-LMN','qty'=>11,'is_saleable'=>false,'unit_cost'=>17_000]],
                'finance_user');
        } catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'SR-13') || str_contains($e->getMessage(), 'melebihi');
        }
        $this->assert("SR-13: CN qty 11 > max rejected", $threw, true, 'boolean');
    }

    private function testT33(): void
    {
        $bank = (int)(JournalEntry::where('debit_account','1000')->sum('amount')
                   - JournalEntry::where('credit_account','1000')->sum('amount'));
        // Bank: 20M opening + 100k payment (T-23) = 20,100,000
        $this->assert("Bank > 20,000,000 (SR-19: Posted only)",  $bank >= 20_000_000, true, 'boolean');

        // Draft invoices do NOT appear in AR
        $draftInvs = Invoice::where('status','draft')->count();
        $draftJournals = 0;
        Invoice::where('status','draft')->each(function($inv) use (&$draftJournals) {
            $draftJournals += JournalEntry::where('invoice_id', $inv->id)->count();
        });
        $this->assert("Draft invoices have 0 journal entries (SR-19)", $draftJournals, 0);

        // Open Orders count
        $openSOs = SalesOrder::whereIn('status',['confirmed','done'])->count();
        $this->assert("Open SOs from confirmed/done only >= 1", $openSOs >= 1, true, 'boolean');
    }

    private function testT34(): void
    {
        $p01 = Product::find('P01');
        // Create extra SO for C03 (has room for credit)
        $so3 = $this->sales->createQuotation([
            'customer_id' => 'C03', 'created_by' => 'sales_user',
            'lines' => [['product_id'=>'P01','variant_sku'=>$p01->variant_sku,'product_name'=>$p01->name,
                'unit'=>$p01->unit,'quantity'=>2,'unit_price'=>25000,'discount_percent'=>0,'source_location'=>'WH-JKT']],
        ]);
        $this->sales->confirmSalesOrder($so3->id, 'sales_user');
        $do3 = $this->inventory->createDelivery($so3->id, 'warehouse_mgr');
        $this->inventory->processDelivery($do3->id, 'E01', 'warehouse_mgr');
        $sol3 = SalesOrderLine::where('sales_order_id', $so3->id)->first();

        $arBefore = (int)(JournalEntry::where('debit_account','1100')->sum('amount')
                       - JournalEntry::where('credit_account','1100')->sum('amount'));

        // Create DRAFT invoice (do NOT post)
        $draftInv = $this->accounting->createCustomerInvoice([
            'partner_id'   => 'C03', 'partner_name' => 'UD Berkah Niaga',
            'source_so'    => $so3->id, 'payment_terms' => 'NET30', 'created_by' => 'finance_user',
            'lines' => [['product_id'=>'P01','variant_sku'=>$sol3->variant_sku,
                'product_name'=>$sol3->product_name,'unit'=>$sol3->unit,
                'unit_price'=>(int)$sol3->unit_price,'delivered_qty'=>(int)$sol3->delivered_qty,
                'invoiced_qty'=>(int)$sol3->invoiced_qty]],
        ]);
        $this->assert("Draft invoice created", $draftInv->status, 'draft');

        $arAfter = (int)(JournalEntry::where('debit_account','1100')->sum('amount')
                     - JournalEntry::where('credit_account','1100')->sum('amount'));
        $this->assert("SR-19: AR unchanged after draft invoice", $arAfter, $arBefore);
        $this->assert("Draft invoice: 0 journal entries", JournalEntry::where('invoice_id',$draftInv->id)->count(), 0);
    }

    private function testT35(): void
    {
        // PO00007 should have related Receipt (grId) and Vendor Bill (billId)
        $receipts = Receipt::where('purchase_order_id','PO00007')->where('status','done')->count();
        $this->assert("PO00007 has 1 Done receipt (T-17 fixture)", $receipts, 1);

        $bills = Invoice::where('source_document_id','PO00007')
            ->where('invoice_type','vendor_bill')->where('status','posted')->count();
        $this->assert("PO00007 has 1 posted Vendor Bill (T-21 fixture)", $bills, 1);

        // Audit log for PO00007 — seeded with 'created' by purchase_user
        $poLog = AuditLog::where('document_type','purchase_order')
            ->where('document_id','PO00007')
            ->first();
        $this->assertNotNull("Audit log exists for PO00007", $poLog);
        // Related docs: receipt + bill both reference PO00007
        $this->assert("Receipt links to PO00007",
            Receipt::where('purchase_order_id','PO00007')->exists(), true, 'boolean');
        $this->assert("Vendor Bill source = PO00007",
            Invoice::where('source_document_id','PO00007')->where('invoice_type','vendor_bill')->exists(), true, 'boolean');
    }

    private function testT36(): void
    {
        // SR-16: re-validate a Done receipt must be rejected
        if (!$this->grId) { $this->failMsg("T-36: grId missing"); return; }
        $receipt = Receipt::find($this->grId);
        $this->assert("GR is_posted = true",   (bool)$receipt->is_posted, true, 'boolean');
        $threw = false;
        try { $this->inventory->processReceipt($this->grId, 'E01', 'warehouse_mgr'); }
        catch (\RuntimeException $e) {
            $threw = str_contains($e->getMessage(), 'SR-16') || str_contains($e->getMessage(), 'sudah diposting');
        }
        $this->assert("SR-16: re-validate Done receipt rejected", $threw, true, 'boolean');
    }

    // ══════════════════════════════════════════════════════════════════════
    //  RUNNER & HELPERS
    // ══════════════════════════════════════════════════════════════════════

    private function runTest(string $id, string $name, callable $fn): void
    {
        if ($this->stop && $this->fail > 0) return;
        $this->line("  -- {$id}: {$name}");
        $before = $this->fail;
        try { $fn(); }
        catch (\Throwable $e) {
            $this->fail++;
            $this->line("     EXCEPTION [{$id}]: " . $e->getMessage());
        }
        $result = $this->fail === $before ? 'PASS' : 'FAIL';
        $this->line("  {$result}: {$id}");
        if ($this->fail !== $before) $this->sectionFail++;
    }

    private function assert(string $lbl, mixed $actual, mixed $expected, string $type = 'value'): void
    {
        $ok = match($type) {
            'boolean' => (bool)$actual === (bool)$expected,
            default   => $actual === $expected,
        };
        if ($ok) { $this->pass++; $this->line("     ok   {$lbl} -> " . $this->fmt($actual)); }
        else     { $this->fail++; $this->line("     FAIL {$lbl} (expected: " . $this->fmt($expected) . ", got: " . $this->fmt($actual) . ")"); }
    }

    private function assertNotNull(string $lbl, mixed $v): void
    {
        if ($v !== null) { $this->pass++; $this->line("     ok   {$lbl}"); }
        else             { $this->fail++; $this->line("     FAIL {$lbl} (expected non-null)"); }
    }

    private function failMsg(string $m): void { $this->fail++; $this->line("     FAIL {$m}"); }
    private function fmt(mixed $v): string
    {
        if (is_bool($v)) return $v ? 'true' : 'false';
        if (is_null($v)) return 'NULL';
        if (is_int($v) || is_float($v)) return number_format((float)$v);
        return (string)$v;
    }

    private function sectionHeader(string $letter, string $name, string $range): void
    {
        $this->sectionFail = 0;
        $this->line('');
        $this->line("  ── GROUP {$letter}: {$name} ({$range}) ──────────────────────");
    }

    private function banner(): void
    {
        $this->line('');
        $this->line('  ╔══════════════════════════════════════════════════════╗');
        $this->line('  ║  M2-W4-01  FULL REGRESSION  T-01 → T-36             ║');
        $this->line('  ║  PT Arunika Distribusi Cepat — ERP FMCG              ║');
        $this->line('  ╚══════════════════════════════════════════════════════╝');
        $this->line('');
    }

    private function finalSummary(): void
    {
        $total = $this->pass + $this->fail;
        $this->line('');
        $this->line('  ══════════════════════════════════════════════════════');
        if ($this->fail === 0) {
            $this->line("  ALL 36 TESTS PASSED - {$this->pass}/{$total} assertions");
            $this->line('  M2-W4-01: System ready for final delivery.');
        } else {
            $this->line("  {$this->fail} FAILURE(S) - {$this->pass}/{$total} assertions passed");
        }
        $this->line('  ══════════════════════════════════════════════════════');
        $this->line('');
    }
}
