<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SalesOrderLine — sales_order_lines
 *
 * SR-12: unit_price and discount_percent become immutable after SO is confirmed.
 */
class SalesOrderLine extends Model
{
    protected $fillable = [
        'sales_order_id',
        'product_id',
        'variant_sku',
        'product_name',
        'unit',
        'quantity',
        'unit_price',
        'discount_percent',
        'is_locked',
        'net_amount',
        'tax_amount',
        'gross_amount',
        'delivered_qty',
        'invoiced_qty',
        'reserved_qty',
        'source_location',
    ];

    protected $casts = [
        'quantity'         => 'integer',
        'unit_price'       => 'integer',
        'discount_percent' => 'decimal:2',
        'is_locked'        => 'boolean',
        'net_amount'       => 'integer',
        'tax_amount'       => 'integer',
        'gross_amount'     => 'integer',
        'delivered_qty'    => 'integer',
        'invoiced_qty'     => 'integer',
        'reserved_qty'     => 'integer',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }

    /**
     * Remaining qty that can be invoiced.
     * SR-02: invoice only delivered qty minus already invoiced.
     */
    public function invoiceableQty(): int
    {
        return max(0, $this->delivered_qty - $this->invoiced_qty);
    }

    /**
     * Remaining qty still to be delivered.
     */
    public function pendingDeliveryQty(): int
    {
        return max(0, $this->quantity - $this->delivered_qty);
    }
}
