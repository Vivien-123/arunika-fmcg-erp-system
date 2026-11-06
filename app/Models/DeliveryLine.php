<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * DeliveryLine — delivery_lines
 * SR-09: lot_id is the FEFO-selected lot for lot-tracked SKUs.
 */
class DeliveryLine extends Model
{
    protected $table = 'delivery_lines';

    protected $fillable = [
        'delivery_id', 'sales_order_line_id', 'product_id', 'variant_sku',
        'product_name', 'unit', 'demand_qty', 'done_qty', 'backorder_qty', 'lot_id',
    ];

    protected $casts = [
        'demand_qty'   => 'integer',
        'done_qty'     => 'integer',
        'backorder_qty'=> 'integer',
    ];

    public function delivery()
    {
        return $this->belongsTo(Delivery::class, 'delivery_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }
}
