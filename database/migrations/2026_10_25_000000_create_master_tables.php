<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master data tables: contacts_customers, contacts_vendors, products,
 * vendor_pricelist, warehouses_locations, opening_stock,
 * product_lots, tax_rates, payment_terms
 *
 * Dataset sesuai PDF Handout §4 (halaman 6-8)
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Customers ────────────────────────────────────────────────────
        Schema::create('contacts_customers', function (Blueprint $table) {
            $table->string('id')->primary();        // C01 … C06
            $table->string('name');
            $table->string('type')->default('B2B');
            $table->string('city')->nullable();
            $table->unsignedBigInteger('credit_limit')->default(0);
            $table->string('payment_terms')->default('NET14'); // CASH|NET14|NET30
            $table->unsignedBigInteger('open_ar')->default(0);
            $table->timestamps();
        });

        // ── Vendors ──────────────────────────────────────────────────────
        Schema::create('contacts_vendors', function (Blueprint $table) {
            $table->string('id')->primary();        // V01 … V04
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('payment_terms')->default('NET14');
            $table->string('category')->nullable();
            $table->unsignedBigInteger('open_ap')->default(0);
            $table->timestamps();
        });

        // ── Products (16 SKU) ─────────────────────────────────────────────
        Schema::create('products', function (Blueprint $table) {
            $table->string('id')->primary();        // P01 … P16
            $table->string('variant_sku')->unique();
            $table->string('name');
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('unit');
            $table->unsignedBigInteger('sales_price');
            $table->unsignedBigInteger('cost');
            $table->string('default_vendor')->nullable();
            $table->boolean('track_lot')->default(false); // P03 = true (FEFO)
            $table->timestamps();
        });

        // ── Vendor Pricelist ──────────────────────────────────────────────
        Schema::create('vendor_pricelist', function (Blueprint $table) {
            $table->id();
            $table->string('vendor_id');
            $table->string('variant_sku');
            $table->string('unit');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedInteger('min_qty')->default(1);
            $table->timestamps();
        });

        // ── Warehouse Locations ───────────────────────────────────────────
        Schema::create('warehouses_locations', function (Blueprint $table) {
            $table->string('code')->primary();     // WH-JKT | HUB-BDG
            $table->string('name');
            $table->string('city')->nullable();
            $table->string('usage')->default('Internal');
            $table->timestamps();
        });

        // ── Opening Stock ─────────────────────────────────────────────────
        Schema::create('opening_stock', function (Blueprint $table) {
            $table->id();
            $table->string('variant_sku')->unique();
            $table->unsignedInteger('wh_jkt_on_hand')->default(0);
            $table->unsignedInteger('wh_jkt_reserved')->default(0);
            $table->unsignedInteger('hub_bdg_on_hand')->default(0);
            $table->unsignedInteger('company_total')->default(0);
            $table->unsignedBigInteger('standard_cost')->default(0);
            $table->unsignedBigInteger('standard_value')->default(0);
            $table->timestamps();
        });

        // ── Product Lots (P03 FEFO) ───────────────────────────────────────
        Schema::create('product_lots', function (Blueprint $table) {
            $table->string('lot_id')->primary();
            $table->string('variant_sku');
            $table->string('location');            // WH-JKT | HUB-BDG
            $table->unsignedInteger('qty');
            $table->date('expiry_date')->nullable();
            $table->string('selection_policy')->default('FEFO');
            $table->timestamps();
        });

        // ── Tax Rates ─────────────────────────────────────────────────────
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('formal_rate');
            $table->string('tax_base_factor');
            $table->decimal('effective_rate', 5, 2);
            $table->string('rounding')->default('Per baris, rupiah terdekat');
            $table->string('scope')->nullable();
            $table->timestamps();
        });

        // ── Payment Terms ─────────────────────────────────────────────────
        Schema::create('payment_terms', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('description');
            $table->unsignedInteger('due_days')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_terms');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('product_lots');
        Schema::dropIfExists('opening_stock');
        Schema::dropIfExists('warehouses_locations');
        Schema::dropIfExists('vendor_pricelist');
        Schema::dropIfExists('products');
        Schema::dropIfExists('contacts_vendors');
        Schema::dropIfExists('contacts_customers');
    }
};
