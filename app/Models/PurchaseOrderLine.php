<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PurchaseOrderLine — purchase_order_lines
 * SR-21: unit_price locked after PO confirmation.
 * SR-06: billable qty = accepted_qty - billed_qty.
 */
class PurchaseOrderLine extends Model
{
    protected $table = 'purchase_order_lines';

    protected $fillable = [
        'purchase_order_id', 'product_id', 'variant_sku', 'product_name',
        'unit', 'quantity', 'unit_price', 'is_locked',
        'net_amount', 'tax_amount', 'gross_amount',
        'received_qty', 'accepted_qty', 'rejected_qty', 'billed_qty',
    ];

    protected $casts = [
        'quantity'     => 'integer',
        'unit_price'   => 'integer',
        'is_locked'    => 'boolean',
        'net_amount'   => 'integer',
        'tax_amount'   => 'integer',
        'gross_amount' => 'integer',
        'received_qty' => 'integer',
        'accepted_qty' => 'integer',
        'rejected_qty' => 'integer',
        'billed_qty'   => 'integer',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }

    /**
     * SR-06: Qty that can still be billed = accepted - already_billed.
     */
    public function billableQty(): int
    {
        return max(0, $this->accepted_qty - $this->billed_qty);
    }
}
