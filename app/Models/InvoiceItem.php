<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * InvoiceItem — one line of an Invoice / Vendor Bill / Credit Note (M4)
 * PPN dihitung per baris (§4 PPN: half-up rounding).
 */
class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'product_id', 'variant_sku', 'product_name', 'unit',
        'quantity', 'unit_price', 'discount_percent',
        'net_amount', 'tax_amount', 'gross_amount',
        'delivery_ref', 'delivered_qty', 'accepted_qty', 'invoiced_qty',
    ];

    protected $casts = ['discount_percent' => 'decimal:2'];

    public function invoice() { return $this->belongsTo(Invoice::class, 'invoice_id'); }

    /** SR-02: remaining invoiceable qty */
    public function invoiceableQty(): int
    {
        return max(0, (int) $this->delivered_qty - (int) $this->invoiced_qty);
    }

    /** SR-06: remaining billable qty */
    public function billableQty(): int
    {
        return max(0, (int) $this->accepted_qty - (int) $this->invoiced_qty);
    }
}
