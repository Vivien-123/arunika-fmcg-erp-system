<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Delivery — deliveries (Delivery Order)
 * State: draft → ready → done | cancelled
 * SR-04: anti-negative stock check on validate.
 * SR-07/09: FEFO lot picking.
 * SR-16: idempotency guard.
 * SR-17: validator must be Active Warehouse employee.
 */
class Delivery extends Model
{
    protected $table = 'deliveries';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'sales_order_id', 'customer_id', 'status',
        'source_location', 'validator_employee_id', 'validator_user',
        'validated_at', 'created_by', 'is_posted', 'idempotency_key',
    ];

    protected $casts = [
        'is_posted'    => 'boolean',
        'validated_at' => 'datetime',
    ];

    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_id', 'id');
    }

    public function lines()
    {
        return $this->hasMany(DeliveryLine::class, 'delivery_id', 'id');
    }

    public function validatorEmployee()
    {
        return $this->belongsTo(Employee::class, 'validator_employee_id', 'id');
    }
}
