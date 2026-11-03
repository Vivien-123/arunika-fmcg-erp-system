<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Product — products
 *
 * Handout §4 columns: id (P01–P16), variant_sku, name, size, color,
 *                     unit, sales_price, cost, default_vendor
 *
 * SR-12 : Sales price locked at SO confirmation — read from this model.
 * SR-21 : Purchase cost locked at PO confirmation — read from this model.
 * SR-03 : Standard cost used for inventory valuation.
 */
class Product extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'id', 'variant_sku', 'name', 'size', 'color',
        'unit', 'sales_price', 'cost', 'default_vendor',
    ];

    protected $casts = [
        'sales_price' => 'integer',
        'cost'        => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function defaultVendor()
    {
        return $this->belongsTo(Vendor::class, 'default_vendor', 'id');
    }

    public function vendorPricelists()
    {
        return $this->hasMany(VendorPricelist::class, 'variant_sku', 'variant_sku');
    }

    public function openingStock()
    {
        return $this->hasOne(OpeningStock::class, 'variant_sku', 'variant_sku');
    }

    public function lots()
    {
        return $this->hasMany(ProductLot::class, 'variant_sku', 'variant_sku');
    }

    // ── Business logic ─────────────────────────────────────────────────────

    /**
     * SR-12 / SR-21: Return the locked price for a given context.
     * 'sales'    → sales_price
     * 'purchase' → cost (standard; override with vendor pricelist if present)
     */
    public function lockedPrice(string $context = 'sales', ?string $vendorId = null): int
    {
        if ($context === 'purchase' && $vendorId) {
            $entry = $this->vendorPricelists()
                          ->where('vendor_id', $vendorId)
                          ->first();
            if ($entry) {
                return $entry->unit_price;
            }
        }
        return $context === 'sales' ? $this->sales_price : $this->cost;
    }

    /** Gross margin percentage */
    public function marginPct(): float
    {
        if ($this->sales_price === 0) {
            return 0.0;
        }
        return round(($this->sales_price - $this->cost) / $this->sales_price * 100, 2);
    }
}
