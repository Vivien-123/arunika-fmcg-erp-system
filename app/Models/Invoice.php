<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Invoice — Customer Invoice, Vendor Bill, Credit Note (M4)
 *
 * SR-02 : invoice hanya untuk qty Done di Delivery.
 * SR-06 : vendor bill dibatasi accepted receipt qty.
 * SR-13 : Credit Note ≤ invoiced − already credited.
 * SR-16 : Unique document number; lifecycle Draft → Posted → Paid.
 * SR-20 : Partial payment; outstanding decreases by paid amount.
 */
class Invoice extends Model
{
    protected $fillable = [
        'document_number', 'invoice_type', 'status',
        'partner_id', 'partner_name', 'partner_type',
        'source_document_id', 'source_document_type', 'original_invoice_id',
        'untaxed_amount', 'tax_amount', 'gross_amount', 'amount_paid', 'amount_outstanding',
        'tax_code', 'tax_rate', 'payment_terms', 'invoice_date', 'due_date',
        'created_by', 'posted_by', 'posted_at',
    ];

    protected $casts = [
        'tax_rate'     => 'decimal:4',
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'posted_at'    => 'datetime',
    ];

    public function items()          { return $this->hasMany(InvoiceItem::class, 'invoice_id'); }
    public function journalEntries() { return $this->hasMany(JournalEntry::class, 'invoice_id'); }
    public function originalInvoice(){ return $this->belongsTo(Invoice::class, 'original_invoice_id'); }
    public function creditNotes()    { return $this->hasMany(Invoice::class, 'original_invoice_id'); }

    public function isDraft(): bool  { return $this->status === 'draft'; }
    public function isPosted(): bool { return $this->status === 'posted'; }
    public function isPaid(): bool   { return $this->status === 'paid'; }
    public function canBePosted(): bool { return $this->status === 'draft'; }

    /** SR-20 */
    public function outstandingAmount(): int
    {
        return max(0, (int) $this->gross_amount - (int) $this->amount_paid);
    }

    /** SR-13: max creditable qty per SKU */
    public function maxCreditableQty(): array
    {
        $alreadyCredited = Invoice::where('original_invoice_id', $this->id)
            ->where('invoice_type', 'credit_note')
            ->where('status', 'posted')
            ->with('items')
            ->get()
            ->flatMap(fn($cn) => $cn->items)
            ->groupBy('variant_sku')
            ->map(fn($g) => $g->sum('quantity'));

        $result = [];
        foreach ($this->items as $item) {
            $credited = $alreadyCredited[$item->variant_sku] ?? 0;
            $result[$item->variant_sku] = max(0, $item->quantity - $credited);
        }
        return $result;
    }

    /** PPN 11% half-up */
    public static function calculateTax(int $netAmount): int
    {
        return (int) floor($netAmount * 0.11 + 0.5);
    }

    public static function calculateLine(int $qty, int $unitPrice, float $discountPct = 0.0): array
    {
        $effectivePrice = (int) round($unitPrice * (1 - $discountPct / 100));
        $net            = $qty * $effectivePrice;
        $tax            = self::calculateTax($net);
        return [
            'qty' => $qty, 'unit_price' => $unitPrice, 'discount_percent' => $discountPct,
            'effective_price' => $effectivePrice, 'net_amount' => $net,
            'tax_amount' => $tax, 'gross_amount' => $net + $tax,
        ];
    }
}
