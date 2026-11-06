<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PurchaseOrder — purchase_orders
 * SR-21: unit_price locked on confirmation.
 */
class PurchaseOrder extends Model
{
    protected $table = 'purchase_orders';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'vendor_id', 'vendor_name', 'status', 'is_locked',
        'net_amount', 'tax_amount', 'gross_amount', 'payment_terms',
        'order_date', 'expected_arrival', 'created_by', 'confirmed_by', 'confirmed_at',
    ];

    protected $casts = [
        'is_locked'    => 'boolean',
        'net_amount'   => 'integer',
        'tax_amount'   => 'integer',
        'gross_amount' => 'integer',
        'confirmed_at' => 'datetime',
        'order_date'   => 'date',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function lines()
    {
        return $this->hasMany(PurchaseOrderLine::class, 'purchase_order_id', 'id');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'purchase_order_id', 'id');
    }

    public function isLocked(): bool
    {
        return $this->is_locked || in_array($this->status, ['confirmed', 'done']);
    }
}
