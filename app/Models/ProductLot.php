<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ProductLot — product_lots
 *
 * Handout §4 columns: lot_id, variant_sku, location, qty,
 *                     expiry_date, selection_policy
 *
 * SR-07 : FEFO (First Expired First Out) lot selection.
 *         When picking stock for a Sales Order, always consume
 *         the lot with the earliest expiry_date first.
 */
class ProductLot extends Model
{
    protected $primaryKey = 'lot_id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'lot_id', 'variant_sku', 'location', 'qty',
        'expiry_date', 'selection_policy',
    ];

    protected $casts = [
        'qty'         => 'integer',
        'expiry_date' => 'date',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }

    public function warehouseLocation()
    {
        return $this->belongsTo(WarehouseLocation::class, 'location', 'code');
    }

    // ── Scopes ─────────────────────────────────────────────────────────────

    /**
     * SR-07: FEFO scope — order lots by expiry date ascending.
     * Always apply this when selecting lots for fulfillment.
     */
    public function scopeFefo($query)
    {
        return $query->orderBy('expiry_date', 'asc');
    }

    /** Filter lots by location code */
    public function scopeAtLocation($query, string $locationCode)
    {
        return $query->where('location', $locationCode);
    }

    /** Only lots that still have stock */
    public function scopeWithStock($query)
    {
        return $query->where('qty', '>', 0);
    }

    // ── Business logic ─────────────────────────────────────────────────────

    public function isExpired(): bool
    {
        return $this->expiry_date->isPast();
    }

    public function daysUntilExpiry(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->expiry_date, false);
    }

    /**
     * SR-07: Get the FEFO-ordered lots for a SKU with available stock.
     * Use this wherever picking logic needs to select lots.
     */
    public static function fefoLotsFor(string $variantSku, ?string $locationCode = null)
    {
        return self::where('variant_sku', $variantSku)
                   ->when($locationCode, fn($q) => $q->where('location', $locationCode))
                   ->withStock()
                   ->fefo()
                   ->get();
    }
}
