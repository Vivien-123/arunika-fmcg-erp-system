<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SalesOrder — sales_orders (F-05, F-06, F-10, F-22 · S-05)
 *
 * SR-03 : Confirmation allowed even when stock is insufficient (backorder).
 * SR-07 : Discount ≤ 10% → auto-confirm; discount > 10% → status 'to_approve'.
 * SR-08 : Only Finance/Owner can approve discounts > 10%.
 * SR-11 : Credit exposure = open_ar + all confirmed unbilled SO gross totals.
 * SR-12 : Prices and lines are locked (is_locked=true) after confirmation.
 * SR-22 : Stock reservation via OpeningStock::reserve(); does NOT change On Hand.
 */
class SalesOrder extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'id',
        'customer_id',
        'customer_name',
        'status',
        'discount_approval_status',
        'discount_approved_by',
        'discount_approved_at',
        'untaxed_amount',
        'tax_amount',
        'gross_amount',
        'payment_terms',
        'order_date',
        'delivery_promise_date',
        'created_by',
        'confirmed_by',
        'confirmed_at',
        'lead_id',
    ];

    protected $casts = [
        'untaxed_amount'       => 'integer',
        'tax_amount'           => 'integer',
        'gross_amount'         => 'integer',
        'discount_approved_at' => 'datetime',
        'confirmed_at'         => 'datetime',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function lines()
    {
        return $this->hasMany(SalesOrderLine::class, 'sales_order_id', 'id');
    }

    // ── Status helpers ─────────────────────────────────────────────────────

    public function isDraft(): bool      { return $this->status === 'draft'; }
    public function isToApprove(): bool  { return $this->status === 'to_approve'; }
    public function isConfirmed(): bool  { return in_array($this->status, ['confirmed', 'done']); }
    public function isCancelled(): bool  { return $this->status === 'cancelled'; }

    /**
     * SR-07/08: Does any line have a discount > 10%?
     */
    public function requiresDiscountApproval(): bool
    {
        return $this->lines()
            ->where('discount_percent', '>', 10.00)
            ->exists();
    }

    /**
     * SR-12: Are all lines locked (prices cannot be edited)?
     */
    public function isLocked(): bool
    {
        return $this->isConfirmed() || $this->isToApprove();
    }

    /** Next sequential SO number (S00008, S00009 …) */
    public static function nextId(): string
    {
        $last = self::orderByDesc('id')->value('id');
        if (!$last) return 'S00001';
        $num = (int) substr($last, 1) + 1;
        return 'S' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
