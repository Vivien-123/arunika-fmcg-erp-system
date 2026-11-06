<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * StockMove — stock_moves (perpetual inventory ledger)
 * SR-03: Every stock movement is recorded here with unit_cost at time of move.
 * SR-16: idempotency_key prevents duplicate moves.
 */
class StockMove extends Model
{
    protected $table = 'stock_moves';

    protected $fillable = [
        'reference', 'move_type', 'variant_sku',
        'from_location', 'to_location', 'qty',
        'unit_cost', 'total_value', 'lot_id',
        'actor_user', 'actor_employee', 'idempotency_key',
    ];

    protected $casts = [
        'qty'         => 'integer',
        'unit_cost'   => 'integer',
        'total_value' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }
}
