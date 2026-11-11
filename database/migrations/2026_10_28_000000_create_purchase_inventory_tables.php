<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration M2 — Purchase & Inventory
 * Tables: purchase_orders, purchase_order_lines, receipts, receipt_lines,
 *         deliveries, delivery_lines
 *
 * SR refs: SR-04, SR-06, SR-09, SR-14, SR-16, SR-17, SR-21, SR-22
 */
return new class extends Migration {
    public function up(): void
    {
        // ── Purchase Orders ───────────────────────────────────────────────
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->string('id')->primary();             // PO00007, PO00008
            $table->string('vendor_id');
            $table->string('vendor_name');
            $table->string('status')->default('rfq');    // rfq | confirmed | done | cancelled
            $table->boolean('is_locked')->default(false);// SR-21: locked on confirm
            $table->unsignedBigInteger('net_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('gross_amount')->default(0);
            $table->string('payment_terms')->nullable();
            $table->date('order_date')->nullable();
            $table->date('expected_arrival')->nullable();
            $table->string('created_by')->nullable();
            $table->string('confirmed_by')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        // ── Purchase Order Lines ───────────────────────────────────────────
        Schema::create('purchase_order_lines', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_order_id');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            $table->string('product_id');
            $table->string('variant_sku');
            $table->string('product_name');
            $table->string('unit');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price');    // SR-21: locked on PO confirm
            $table->unsignedBigInteger('net_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('gross_amount')->default(0);
            $table->unsignedInteger('received_qty')->default(0);
            $table->unsignedInteger('accepted_qty')->default(0); // SR-06: only accepted goes to stock
            $table->unsignedInteger('rejected_qty')->default(0);
            $table->unsignedInteger('billed_qty')->default(0);
            $table->timestamps();
        });

        // ── Receipts (Goods Receipt) ───────────────────────────────────────
        Schema::create('receipts', function (Blueprint $table) {
            $table->string('id')->primary();             // GR-00001
            $table->string('purchase_order_id');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            $table->string('vendor_id')->nullable();
            $table->string('status')->default('draft');  // draft | done | cancelled
            $table->string('destination_location')->default('WH-JKT');
            $table->string('validator_employee_id')->nullable(); // SR-17
            $table->string('validator_user')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->string('created_by')->nullable();
            $table->boolean('is_posted')->default(false);// SR-16
            $table->string('idempotency_key')->unique()->nullable(); // SR-16
            $table->timestamps();
        });

        // ── Receipt Lines ─────────────────────────────────────────────────
        Schema::create('receipt_lines', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_id');
            $table->foreign('receipt_id')->references('id')->on('receipts')->cascadeOnDelete();
            $table->string('purchase_order_line_id')->nullable();
            $table->string('product_id');
            $table->string('variant_sku');
            $table->string('product_name');
            $table->string('unit');
            $table->unsignedInteger('ordered_qty')->default(0);
            $table->unsignedInteger('received_qty')->default(0);
            $table->unsignedInteger('accepted_qty')->default(0); // SR-06
            $table->unsignedInteger('rejected_qty')->default(0);
            $table->string('lot_id')->nullable();
            $table->timestamps();
        });

        // ── Deliveries (Delivery Order) ────────────────────────────────────
        Schema::create('deliveries', function (Blueprint $table) {
            $table->string('id')->primary();             // DO-00001
            $table->string('sales_order_id')->nullable();
            $table->string('customer_id')->nullable();
            $table->string('status')->default('draft');  // draft | ready | done | cancelled
            $table->string('source_location')->default('WH-JKT');
            $table->string('validator_employee_id')->nullable(); // SR-17
            $table->string('validator_user')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->string('created_by')->nullable();
            $table->boolean('is_posted')->default(false);// SR-16
            $table->string('idempotency_key')->unique()->nullable(); // SR-16
            $table->timestamps();
        });

        // ── Delivery Lines ────────────────────────────────────────────────
        Schema::create('delivery_lines', function (Blueprint $table) {
            $table->id();
            $table->string('delivery_id');
            $table->foreign('delivery_id')->references('id')->on('deliveries')->cascadeOnDelete();
            $table->string('sales_order_line_id')->nullable();
            $table->string('product_id');
            $table->string('variant_sku');
            $table->string('product_name');
            $table->string('unit');
            $table->unsignedInteger('demand_qty')->default(0);
            $table->unsignedInteger('done_qty')->default(0);     // actual delivered
            $table->unsignedInteger('backorder_qty')->default(0);
            $table->string('lot_id')->nullable();                 // SR-09 FEFO lot
            $table->string('source_location')->default('WH-JKT');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_lines');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('receipt_lines');
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('purchase_order_lines');
        Schema::dropIfExists('purchase_orders');
    }
};
