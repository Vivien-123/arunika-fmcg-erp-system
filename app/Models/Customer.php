<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Customer — contacts_customers
 *
 * Handout §4 columns: id (C01–C06), name, type, city,
 *                     credit_limit, payment_terms, open_ar
 *
 * SR-05 : Credit limit check on Sales Order confirm.
 */
class Customer extends Model
{
    protected $table      = 'contacts_customers';
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'id', 'name', 'type', 'city',
        'credit_limit', 'payment_terms', 'open_ar',
    ];

    protected $casts = [
        'credit_limit' => 'integer',
        'open_ar'      => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    /** Invoices raised against this customer */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'partner_id', 'id')
                    ->where('partner_type', 'customer');
    }

    // ── Business logic ─────────────────────────────────────────────────────

    /** Remaining credit available = limit − current open AR */
    public function availableCredit(): int
    {
        return max(0, $this->credit_limit - $this->open_ar);
    }

    /**
     * SR-05: Would adding $orderGross push the customer over their credit limit?
     */
    public function wouldBreachCreditLimit(int $orderGross): bool
    {
        return ($this->open_ar + $orderGross) > $this->credit_limit;
    }

    public function isCashCustomer(): bool
    {
        return $this->payment_terms === 'Tunai';
    }
}
