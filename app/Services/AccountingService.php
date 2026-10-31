<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

/**
 * AccountingService — M4 Core Business Logic (Laravel / PHP layer)
 *
 * Mirrors accounting_invoicing_service.js for the Laravel web UI.
 * SR-01 vs SR-02 → SR-02 wins.
 * SR-05 vs SR-06 → SR-06 wins.
 */
class AccountingService
{
    // COA codes
    const BANK         = '1000';
    const AR           = '1100';
    const INVENTORY    = '1300';
    const INTERIM      = '1350';
    const VAT_INPUT    = '1400';
    const AP           = '2000';
    const VAT_OUTPUT   = '2100';
    const EQUITY       = '3000';
    const SALES        = '4000';
    const SALES_RETURN = '4100';
    const COGS         = '5100';

    // ── Sequence ───────────────────────────────────────────────────────────
    private function nextInvoiceNumber(string $type): string
    {
        $prefix = match ($type) {
            'customer_invoice' => 'INV',
            'vendor_bill'      => 'BILL',
            'credit_note'      => 'CN',
            default            => 'DOC',
        };
        $last = Invoice::where('document_number', 'like', "{$prefix}-%")
                       ->orderByDesc('id')->value('document_number');
        $seq = $last ? ((int) substr($last, strlen($prefix) + 1)) + 1 : 1;
        return sprintf('%s-%05d', $prefix, $seq);
    }

    // ── PPN Tax ────────────────────────────────────────────────────────────
    public function calcTax(int $netAmount): int
    {
        return (int) floor($netAmount * 0.11 + 0.5);
    }

    public function calcLine(int $qty, int $unitPrice, float $discountPct = 0.0): array
    {
        $effectivePrice = (int) round($unitPrice * (1 - $discountPct / 100));
        $net            = $qty * $effectivePrice;
        $tax            = $this->calcTax($net);
        return ['qty' => $qty, 'unit_price' => $unitPrice, 'discount_percent' => $discountPct,
                'effective_price' => $effectivePrice, 'net_amount' => $net,
                'tax_amount' => $tax, 'gross_amount' => $net + $tax];
    }

    // ── F-09: Customer Invoice ─────────────────────────────────────────────
    public function createCustomerInvoice(array $params): Invoice
    {
        return DB::transaction(function () use ($params) {
            $netTotal = 0; $taxTotal = 0; $computedLines = [];
            foreach ($params['lines'] ?? [] as $line) {
                $qty = max(0, (int)($line['delivered_qty'] ?? 0) - (int)($line['invoiced_qty'] ?? 0));
                if ($qty <= 0) continue;
                $calc = $this->calcLine($qty, (int)$line['unit_price']);
                $netTotal += $calc['net_amount']; $taxTotal += $calc['tax_amount'];
                $computedLines[] = array_merge($line, $calc, ['quantity' => $qty]);
            }
            if (empty($computedLines)) throw new \RuntimeException('SR-02 Enforcement: Tidak ada kuantitas yang sudah terkirim untuk ditagih.');

            $grossTotal = $netTotal + $taxTotal;
            $docNumber  = $this->nextInvoiceNumber('customer_invoice');
            $termDays   = match ($params['payment_terms'] ?? 'NET14') { 'CASH' => 0, 'NET30' => 30, default => 14 };

            $invoice = Invoice::create([
                'document_number' => $docNumber, 'invoice_type' => 'customer_invoice', 'status' => 'draft',
                'partner_id' => $params['partner_id'], 'partner_name' => $params['partner_name'], 'partner_type' => 'customer',
                'source_document_id' => $params['source_so'] ?? null, 'source_document_type' => 'sales_order',
                'untaxed_amount' => $netTotal, 'tax_amount' => $taxTotal, 'gross_amount' => $grossTotal,
                'amount_paid' => 0, 'amount_outstanding' => $grossTotal,
                'tax_code' => 'PPN-NONLUX', 'tax_rate' => 0.1100,
                'payment_terms' => $params['payment_terms'] ?? 'NET14',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays($termDays)->toDateString(),
                'created_by' => $params['created_by'],
            ]);
            foreach ($computedLines as $line) {
                InvoiceItem::create(array_merge(['invoice_id' => $invoice->id, 'delivered_qty' => $line['delivered_qty'] ?? $line['quantity'], 'accepted_qty' => 0, 'invoiced_qty' => $line['invoiced_qty'] ?? 0], $line));
            }
            AuditLog::record('customer_invoice', $docNumber, 'created', $params['created_by'], "Customer Invoice {$docNumber} dibuat. Net: Rp{$netTotal}, PPN: Rp{$taxTotal}, Gross: Rp{$grossTotal}. SR-02 applied.");
            return $invoice;
        });
    }

    // ── F-13: Vendor Bill (3-Way Match) ────────────────────────────────────
    public function createVendorBill(array $params): Invoice
    {
        return DB::transaction(function () use ($params) {
            $netTotal = 0; $taxTotal = 0; $computedLines = [];
            foreach ($params['lines'] ?? [] as $line) {
                $qty = max(0, (int)($line['accepted_qty'] ?? 0) - (int)($line['already_billed'] ?? 0));
                if ($qty <= 0) continue;
                $calc = $this->calcLine($qty, (int)$line['unit_price']);
                $netTotal += $calc['net_amount']; $taxTotal += $calc['tax_amount'];
                $computedLines[] = array_merge($line, $calc, ['quantity' => $qty]);
            }
            if (empty($computedLines)) throw new \RuntimeException('SR-06 Enforcement: Tidak ada accepted receipt qty yang belum ditagih.');

            $grossTotal = $netTotal + $taxTotal;
            $docNumber  = $this->nextInvoiceNumber('vendor_bill');
            $termDays   = match ($params['payment_terms'] ?? 'NET14') { 'CASH' => 0, 'NET30' => 30, default => 14 };

            $invoice = Invoice::create([
                'document_number' => $docNumber, 'invoice_type' => 'vendor_bill', 'status' => 'draft',
                'partner_id' => $params['partner_id'], 'partner_name' => $params['partner_name'], 'partner_type' => 'vendor',
                'source_document_id' => $params['source_po'] ?? null, 'source_document_type' => 'purchase_order',
                'untaxed_amount' => $netTotal, 'tax_amount' => $taxTotal, 'gross_amount' => $grossTotal,
                'amount_paid' => 0, 'amount_outstanding' => $grossTotal,
                'tax_code' => 'PPN-NONLUX', 'tax_rate' => 0.1100,
                'payment_terms' => $params['payment_terms'] ?? 'NET14',
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays($termDays)->toDateString(),
                'created_by' => $params['created_by'],
            ]);
            foreach ($computedLines as $line) {
                InvoiceItem::create(array_merge(['invoice_id' => $invoice->id, 'delivered_qty' => 0, 'accepted_qty' => $line['accepted_qty'] ?? $line['quantity'], 'invoiced_qty' => $line['already_billed'] ?? 0], $line));
            }
            AuditLog::record('vendor_bill', $docNumber, 'created', $params['created_by'], "Vendor Bill {$docNumber} dibuat (3-way match). SR-06 applied.");
            return $invoice;
        });
    }

    // ── F-14: Post Invoice → Journal Entries ──────────────────────────────
    public function postInvoice(Invoice $invoice, string $postedBy): Invoice
    {
        if (!$invoice->canBePosted()) throw new \RuntimeException("SR-16 Enforcement: Invoice {$invoice->document_number} sudah diposting (status: {$invoice->status}). Double posting dilarang.");

        return DB::transaction(function () use ($invoice, $postedBy) {
            $ref = $invoice->document_number;
            if ($invoice->invoice_type === 'customer_invoice') {
                $this->postEntry($invoice, 'sales', $ref, self::AR, self::SALES, $invoice->untaxed_amount, "Penjualan - {$ref} (net)", $postedBy);
                $this->postEntry($invoice, 'sales', $ref.'-TAX', self::AR, self::VAT_OUTPUT, $invoice->tax_amount, "PPN Keluaran - {$ref}", $postedBy);
            } elseif ($invoice->invoice_type === 'vendor_bill') {
                $this->postEntry($invoice, 'purchase', $ref, self::INVENTORY, self::AP, $invoice->untaxed_amount, "Pembelian - {$ref} (net)", $postedBy);
                $this->postEntry($invoice, 'purchase', $ref.'-TAX', self::VAT_INPUT, self::AP, $invoice->tax_amount, "PPN Masukan - {$ref}", $postedBy);
            } elseif ($invoice->invoice_type === 'credit_note') {
                $this->postEntry($invoice, 'sales', $ref, self::SALES_RETURN, self::AR, $invoice->untaxed_amount, "Retur Penjualan - {$ref} (net)", $postedBy);
                $this->postEntry($invoice, 'sales', $ref.'-TAX', self::VAT_OUTPUT, self::AR, $invoice->tax_amount, "PPN reversal - {$ref}", $postedBy);
            }
            $invoice->update(['status' => 'posted', 'posted_by' => $postedBy, 'posted_at' => now()]);
            AuditLog::record($invoice->invoice_type, $ref, 'posted', $postedBy, "Invoice {$ref} diposting. SR-16: idempotent.");
            return $invoice->fresh();
        });
    }

    // ── F-15: Register Payment ────────────────────────────────────────────
    public function registerPayment(Invoice $invoice, int $amount, string $paymentRef, string $postedBy): Invoice
    {
        if (!$invoice->isPosted()) throw new \RuntimeException("Pembayaran hanya dapat dilakukan pada Invoice status 'posted'. Status: {$invoice->status}.");
        $outstanding = $invoice->outstandingAmount();
        if ($amount > $outstanding) throw new \RuntimeException("SR-20 Enforcement: Pembayaran Rp{$amount} melebihi saldo outstanding Rp{$outstanding}. Overpayment ditolak.");

        return DB::transaction(function () use ($invoice, $amount, $paymentRef, $postedBy) {
            if ($invoice->invoice_type === 'customer_invoice') {
                $this->postRawEntry(null, 'payment', $paymentRef, self::BANK, self::AR, $amount, "Pembayaran Customer {$invoice->document_number}", $postedBy);
            } else {
                $this->postRawEntry(null, 'payment', $paymentRef, self::AP, self::BANK, $amount, "Pembayaran Vendor {$invoice->document_number}", $postedBy);
            }
            $newPaid        = (int) $invoice->amount_paid + $amount;
            $newOutstanding = max(0, $invoice->outstandingAmount() - $amount);
            $invoice->update(['amount_paid' => $newPaid, 'amount_outstanding' => $newOutstanding, 'status' => $newOutstanding <= 0 ? 'paid' : 'posted']);
            AuditLog::record($invoice->invoice_type, $invoice->document_number, 'payment_registered', $postedBy, "Pembayaran Rp{$amount}. Saldo baru: Rp{$newOutstanding}. SR-20 applied.");
            return $invoice->fresh();
        });
    }

    // ── F-16: Credit Note ────────────────────────────────────────────────
    public function createCreditNote(Invoice $originalInvoice, array $returnLines, string $createdBy): Invoice
    {
        if (!$originalInvoice->isPosted()) throw new \RuntimeException('Credit Note hanya dapat dibuat dari Invoice yang sudah diposting.');
        $maxQty = $originalInvoice->maxCreditableQty();

        return DB::transaction(function () use ($originalInvoice, $returnLines, $createdBy, $maxQty) {
            $netTotal = 0; $taxTotal = 0; $computedLines = [];
            foreach ($returnLines as $ret) {
                $sku = $ret['variant_sku']; $qtyReq = (int)$ret['qty'];
                if ($qtyReq > ($maxQty[$sku] ?? 0)) throw new \RuntimeException("SR-13 Enforcement: Return qty {$qtyReq} untuk SKU {$sku} melebihi batas yang dapat dikreditkan ({$maxQty[$sku]}).");
                $origItem = $originalInvoice->items->firstWhere('variant_sku', $sku);
                $unitPrice = $origItem ? (int)$origItem->unit_price : 0;
                $calc = $this->calcLine($qtyReq, $unitPrice);
                $netTotal += $calc['net_amount']; $taxTotal += $calc['tax_amount'];
                $computedLines[] = array_merge($ret, $calc, ['quantity' => $qtyReq, 'unit_price' => $unitPrice, 'product_id' => $origItem->product_id ?? '', 'product_name' => $origItem->product_name ?? $sku, 'unit' => $origItem->unit ?? 'unit']);
            }
            $grossTotal = $netTotal + $taxTotal;
            $docNumber  = $this->nextInvoiceNumber('credit_note');
            $creditNote = Invoice::create(['document_number' => $docNumber, 'invoice_type' => 'credit_note', 'status' => 'draft', 'partner_id' => $originalInvoice->partner_id, 'partner_name' => $originalInvoice->partner_name, 'partner_type' => 'customer', 'source_document_id' => $originalInvoice->source_document_id, 'source_document_type' => 'sales_order', 'original_invoice_id' => $originalInvoice->id, 'untaxed_amount' => $netTotal, 'tax_amount' => $taxTotal, 'gross_amount' => $grossTotal, 'amount_paid' => 0, 'amount_outstanding' => $grossTotal, 'tax_code' => 'PPN-NONLUX', 'tax_rate' => 0.1100, 'created_by' => $createdBy, 'invoice_date' => now()->toDateString()]);
            foreach ($computedLines as $line) {
                InvoiceItem::create(array_merge(['invoice_id' => $creditNote->id], $line));
                if (($ret['is_saleable'] ?? false) && ($ret['unit_cost'] ?? 0) > 0) {
                    $reversalAmt = (int)$ret['qty'] * (int)$ret['unit_cost'];
                    $this->postRawEntry(null, 'stock', "COGS-REVERSAL-{$docNumber}", self::INVENTORY, self::COGS, $reversalAmt, "COGS Reversal retur layak jual - {$docNumber}", $createdBy);
                }
            }
            AuditLog::record('credit_note', $docNumber, 'created', $createdBy, "Credit Note {$docNumber} dibuat. SR-13 ceiling applied.");
            return $creditNote;
        });
    }

    // ── 3-Way Match ───────────────────────────────────────────────────────
    public function validateThreeWayMatch(string $poId, string $variantSku, int $orderedQty, int $acceptedQty, int $alreadyBilled, int $requestBillQty): array
    {
        $violations  = [];
        $billableQty = max(0, $acceptedQty - $alreadyBilled);
        if ($acceptedQty <= 0)          $violations[] = "Receipt belum ada atau accepted qty = 0 untuk {$variantSku} pada {$poId}.";
        if ($requestBillQty > $billableQty) $violations[] = "SR-06: Request bill qty {$requestBillQty} melebihi billable qty {$billableQty} (accepted {$acceptedQty} − already billed {$alreadyBilled}).";
        if ($requestBillQty > $orderedQty)  $violations[] = "SR-05/SR-21: Request bill qty {$requestBillQty} melebihi ordered qty {$orderedQty}.";
        return ['po_id' => $poId, 'variant_sku' => $variantSku, 'ordered_qty' => $orderedQty, 'accepted_qty' => $acceptedQty, 'already_billed' => $alreadyBilled, 'billable_qty' => $billableQty, 'request_bill_qty' => $requestBillQty, 'is_valid' => empty($violations), 'violations' => $violations];
    }

    // ── Internals ─────────────────────────────────────────────────────────
    private function postEntry(Invoice $invoice, string $journalType, string $reference, string $debitAcc, string $creditAcc, int $amount, string $description, string $postedBy): ?JournalEntry
    {
        return $this->postRawEntry($invoice, $journalType, $reference, $debitAcc, $creditAcc, $amount, $description, $postedBy);
    }

    private function postRawEntry(?Invoice $invoice, string $journalType, string $reference, string $debitAcc, string $creditAcc, int $amount, string $description, string $postedBy): JournalEntry
    {
        $idemKey = JournalEntry::buildIdempotencyKey($journalType, $reference, $debitAcc, $creditAcc, $amount);
        if (JournalEntry::alreadyPosted($idemKey)) return JournalEntry::where('idempotency_key', $idemKey)->first();
        return JournalEntry::create(['invoice_id' => $invoice?->id, 'journal_type' => $journalType, 'reference' => $reference, 'idempotency_key' => $idemKey, 'debit_account' => $debitAcc, 'credit_account' => $creditAcc, 'amount' => $amount, 'description' => $description, 'posted_by' => $postedBy, 'posted_at' => now(), 'is_reversed' => false]);
    }
}
