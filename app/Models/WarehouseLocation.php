<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * WarehouseLocation — warehouses_locations
 *
 * Handout §4 columns: code (WH-JKT | HUB-BDG), name, city, usage
 *
 * SR-08 : Multi-location stock tracking.
 */
class WarehouseLocation extends Model
{
    protected $table      = 'warehouses_locations';
    protected $primaryKey = 'code';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = ['code', 'name', 'city', 'usage'];

    // ── Relationships ──────────────────────────────────────────────────────

    /** Opening stock rows at this location */
    public function openingStockLines()
    {
        // opening_stock is company-level (one row per SKU), not per location.
        // Use ProductLot for per-location breakdown.
        return $this->hasMany(ProductLot::class, 'location', 'code');
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    public function isInternal(): bool
    {
        return $this->usage === 'Internal';
    }
}
