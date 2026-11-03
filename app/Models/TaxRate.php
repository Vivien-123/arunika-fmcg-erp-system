<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * TaxRate — tax_rates
 *
 * Handout §4 data:
 *   code         : PPN-NONLUX
 *   name         : PPN Barang Kena Pajak Non-Mewah
 *   rate         : 0.12   (12% statutory rate)
 *   tax_base_factor: 0.9167  (11/12 — effective tax = 11% of gross)
 *   rounding     : per_line
 *   is_active    : true
 *
 * Why two rates?
 *   Indonesia VAT law sets the statutory rate at 12%, but the tax base
 *   for standard goods is multiplied by 11/12 first, making the effective
 *   rate 11%.  Formula per line:
 *     tax_amount = FLOOR( net_amount × (11/12) × 0.12 + 0.5 )
 *               = FLOOR( net_amount × 0.11 + 0.5 )          ← half-up rounding
 */
class TaxRate extends Model
{
    protected $table     = 'tax_rates';
    protected $fillable  = [
        'code', 'name', 'rate', 'tax_base_factor', 'rounding', 'is_active',
    ];

    protected $casts = [
        'rate'            => 'decimal:4',
        'tax_base_factor' => 'decimal:4',
        'is_active'       => 'boolean',
    ];

    // ── Business logic ─────────────────────────────────────────────────────

    /**
     * Calculate the tax amount for a given net amount using this rate.
     * Half-up rounding applied per transaction line.
     *
     * @param  int $netAmount  Net (pre-tax) amount in Rupiah
     * @return int             Tax amount in Rupiah
     */
    public function calculateTax(int $netAmount): int
    {
        return (int) floor($netAmount * (float) $this->tax_base_factor * (float) $this->rate + 0.5);
    }

    /**
     * Convenience: resolve the active PPN-NONLUX rate (the only one used by
     * this ERP) without hard-coding the rate value throughout the codebase.
     */
    public static function ppnNonLux(): self
    {
        return self::where('code', 'PPN-NONLUX')->where('is_active', true)->firstOrFail();
    }
}
