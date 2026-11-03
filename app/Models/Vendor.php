<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Vendor — contacts_vendors
 *
 * Handout §4 columns: id (V01–V04), name, city, payment_terms, category
 */
class Vendor extends Model
{
    protected $table      = 'contacts_vendors';
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'id', 'name', 'city', 'payment_terms', 'category',
    ];

    // ── Relationships ──────────────────────────────────────────────────────

    /** Products that list this vendor as their default supplier */
    public function products()
    {
        return $this->hasMany(Product::class, 'default_vendor', 'id');
    }

    /** Pricelist entries for this vendor */
    public function pricelist()
    {
        return $this->hasMany(VendorPricelist::class, 'vendor_id', 'id');
    }

    /** Vendor bills (invoices of type vendor_bill) */
    public function bills()
    {
        return $this->hasMany(Invoice::class, 'partner_id', 'id')
                    ->where('partner_type', 'vendor');
    }

    // ── Business logic ─────────────────────────────────────────────────────

    /** Resolve unit price for a given SKU from this vendor's pricelist */
    public function priceFor(string $variantSku): ?int
    {
        $entry = $this->pricelist()->where('variant_sku', $variantSku)->first();
        return $entry?->unit_price;
    }
}
