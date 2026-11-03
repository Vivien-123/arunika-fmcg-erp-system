<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * OpeningStock — opening_stock
 *
 * Handout §4 columns: variant_sku, WH-JKT_on_hand, HUB-BDG_on_hand,
 *                     company_total, standard_cost, standard_value
 *
 * SQL-safe column names used (hyphens not allowed in identifiers):
 *   wh_jkt_on_hand  → "WH-JKT_on_hand"
 *   hub_bdg_on_hand → "HUB-BDG_on_hand"
 *
 * SR-03 : Standard cost valuation. On-hand tracked per location.
 * SR-22 : reserved column tracks stock committed to confirmed Sales Orders.
 */
class OpeningStock extends Model
{
    protected $primaryKey = 'variant_sku'; // unique per SKU at company level
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'variant_sku',
        'wh_jkt_on_hand',   // WH-JKT location qty
        'hub_bdg_on_hand',  // HUB-BDG location qty
        'company_total',
        'reserved',
        'standard_cost',
        'standard_value',
    ];

    protected $casts = [
        'wh_jkt_on_hand'  => 'integer',
        'hub_bdg_on_hand' => 'integer',
        'company_total'   => 'integer',
        'reserved'        => 'integer',
        'standard_cost'   => 'integer',
        'standard_value'  => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }

    // ── Business logic ─────────────────────────────────────────────────────

    /**
     * SR-22: Available qty = on-hand total minus reserved.
     * Used by Sales Order reservation checks.
     */
    public function availableQty(): int
    {
        return max(0, $this->company_total - $this->reserved);
    }

    /**
     * SR-22: Reserve qty for a Sales Order.
     * Throws if insufficient available stock.
     */
    public function reserve(int $qty): void
    {
        if ($qty > $this->availableQty()) {
            throw new \RuntimeException(
                "Stok tidak cukup untuk {$this->variant_sku}. "
                . "Tersedia: {$this->availableQty()}, diminta: {$qty}."
            );
        }
        $this->increment('reserved', $qty);
    }

    /**
     * Release previously reserved qty (e.g. on SO cancel).
     */
    public function releaseReservation(int $qty): void
    {
        $release = min($qty, $this->reserved);
        $this->decrement('reserved', $release);
    }

    /**
     * On-hand qty at a specific location.
     * Accepts 'WH-JKT' or 'HUB-BDG'.
     */
    public function onHandAt(string $locationCode): int
    {
        return match (strtoupper($locationCode)) {
            'WH-JKT'  => $this->wh_jkt_on_hand,
            'HUB-BDG' => $this->hub_bdg_on_hand,
            default   => 0,
        };
    }
}
