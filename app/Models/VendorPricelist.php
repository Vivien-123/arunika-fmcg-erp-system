<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * VendorPricelist — vendor_pricelist
 *
 * Handout §4 columns: vendor_id, variant_sku, unit, unit_price, min_qty
 *
 * SR-21 : Unit price is locked at PO confirmation from this pricelist.
 */
class VendorPricelist extends Model
{
    protected $table = 'vendor_pricelist';

    protected $fillable = [
        'vendor_id', 'variant_sku', 'unit', 'unit_price', 'min_qty',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'min_qty'    => 'integer',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'variant_sku', 'variant_sku');
    }
}
