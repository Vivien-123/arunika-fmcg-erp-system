<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Receipt — receipts (Goods Receipt)
 * SR-06: only accepted_qty updates stock.
 * SR-16: is_posted + idempotency_key prevent double-posting.
 * SR-17: validator_employee_id must be Active Warehouse employee.
 */
class Receipt extends Model
{
    protected $table = 'receipts';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'purchase_order_id', 'vendor_id', 'status',
        'destination_location', 'validator_employee_id', 'validator_user',
        'validated_at', 'created_by', 'is_posted', 'idempotency_key',
    ];

    protected $casts = [
        'is_posted'    => 'boolean',
        'validated_at' => 'datetime',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'id');
    }

    public function lines()
    {
        return $this->hasMany(ReceiptLine::class, 'receipt_id', 'id');
    }

    public function validatorEmployee()
    {
        return $this->belongsTo(Employee::class, 'validator_employee_id', 'id');
    }
}
