<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ReceiptLine — receipt_lines
 * SR-06: accepted_qty is the qty that goes to stock (not rejected_qty).
 */
class ReceiptLine extends Model
{
    protected $table = 'receipt_lines';

    protected $fillable = [
        'receipt_id', 'purchase_order_line_id', 'product_id', 'variant_sku',
        'product_name', 'unit', 'ordered_qty', 'received_qty',
        'accepted_qty', 'rejected_qty', 'lot_id', 'expiry_date',
    ];

    protected $casts = [
        'ordered_qty'  => 'integer',
        'received_qty' => 'integer',
        'accepted_qty' => 'integer',
        'rejected_qty' => 'integer',
        'expiry_date'  => 'date',
    ];

    public function receipt()
    {
        return $this->belongsTo(Receipt::class, 'receipt_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }
}
