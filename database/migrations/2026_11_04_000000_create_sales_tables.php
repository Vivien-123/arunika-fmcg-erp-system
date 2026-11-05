<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration M1 — Sales & CRM
 * Tables: leads, sales_orders, sales_order_lines
 *
 * SR refs: SR-03, SR-07, SR-08, SR-11, SR-12, SR-19, SR-22
 * Feature refs: F-04, F-05, F-06, F-10, F-22
 * Tests: T-03, T-04, T-07, T-08, T-09, T-10
 */
return new class extends Migration {
    public function up(): void
    {
        // ── 1. Leads (CRM) ────────────────────────────────────────────────
        Schema::create('leads', function (Blueprint $table) {
            $table->string('id')->primary();        // L00001, L00002, L00003 …
            $table->string('partner_id')->nullable();
            $table->string('partner_name')->nullable();
            $table->string('stage');                // new | qualified | won | lost
            $table->string('salesperson_id')->nullable();
            $table->string('salesperson_name')->nullable();
            $table->unsignedBigInteger('expected_revenue')->default(0);
            $table->text('notes')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();
        });

        // ── 2. Sales Orders (Quotation → Confirmed → Done) ────────────────
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->string('id')->primary();        // S00007, S00008 …
            $table->string('customer_id');
            $table->foreign('customer_id')->references('id')->on('contacts_customers');
            $table->string('customer_name');

            // Status: draft | to_approve | confirmed | done | cancelled
            $table->string('status')->default('draft');

            // Discount approval workflow (SR-07/08)
            $table->string('discount_approval_status')->default('not_required');
            // not_required | pending | approved | rejected
            $table->string('discount_approved_by')->nullable();
            $table->timestamp('discount_approved_at')->nullable();

            $table->unsignedBigInteger('untaxed_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('gross_amount')->default(0);

            $table->string('payment_terms')->nullable();
            $table->date('order_date')->nullable();
            $table->date('delivery_promise_date')->nullable();  // SR-03: max 3-day promise

            $table->string('created_by')->nullable();
            $table->string('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();

            $table->string('lead_id')->nullable();

            $table->timestamps();
        });

        // ── 3. Sales Order Lines ──────────────────────────────────────────
        Schema::create('sales_order_lines', function (Blueprint $table) {
            $table->id();
            $table->string('sales_order_id');
            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->cascadeOnDelete();

            $table->string('product_id');
            $table->string('variant_sku');
            $table->string('product_name');
            $table->string('unit');

            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price');           // locked on confirm (SR-12)
            $table->decimal('discount_percent', 5, 2)->default(0.00);
            $table->boolean('is_locked')->default(false);       // SR-12: true after confirm

            $table->unsignedBigInteger('net_amount');           // qty × unit_price × (1 - disc)
            $table->unsignedBigInteger('tax_amount');           // PPN 11% per line
            $table->unsignedBigInteger('gross_amount');         // net + tax

            // Delivery tracking (for invoicing SR-02)
            $table->unsignedInteger('delivered_qty')->default(0);
            $table->unsignedInteger('invoiced_qty')->default(0);

            // Stock reservation (SR-22)
            $table->unsignedInteger('reserved_qty')->default(0);
            $table->string('source_location')->default('WH-JKT');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_lines');
        Schema::dropIfExists('sales_orders');
        Schema::dropIfExists('leads');
    }
};
