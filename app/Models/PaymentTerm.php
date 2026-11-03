<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PaymentTerm — payment_terms
 *
 * Handout §4 data:
 *   code    name       days   description
 *   CASH    Tunai         0   Payment at point of sale, no credit extended
 *   NET14   14 Hari      14   Invoice due 14 calendar days after invoice date
 *   NET30   30 Hari      30   Invoice due 30 calendar days after invoice date
 *
 * Used by:
 *   - contacts_customers.payment_terms  (stored as the human-readable label
 *     "Tunai" / "14 hari" / "30 hari" per Handout §4)
 *   - invoices.payment_terms            (same label stored on the invoice)
 *   - due date calculation on invoice posting
 */
class PaymentTerm extends Model
{
    protected $table    = 'payment_terms';
    protected $fillable = ['code', 'name', 'days', 'description'];

    protected $casts = [
        'days' => 'integer',
    ];

    // ── Business logic ─────────────────────────────────────────────────────

    /**
     * Calculate the due date from a given invoice date.
     *
     * @param  \Illuminate\Support\Carbon|string $invoiceDate
     * @return \Illuminate\Support\Carbon
     */
    public function dueDate($invoiceDate): \Illuminate\Support\Carbon
    {
        return \Illuminate\Support\Carbon::parse($invoiceDate)->addDays($this->days);
    }

    public function isCash(): bool
    {
        return $this->days === 0;
    }

    /**
     * Resolve a PaymentTerm from the human-readable label stored in
     * contacts_customers / invoices (e.g. "Tunai", "14 hari", "30 hari").
     */
    public static function fromLabel(string $label): ?self
    {
        return self::whereRaw('LOWER(name) = ?', [strtolower(trim($label))])->first();
    }
}
