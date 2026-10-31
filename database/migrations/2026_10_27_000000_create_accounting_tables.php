<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration M4 — Accounting & Invoicing
 * Tables: chart_of_accounts, invoices, invoice_items, journal_entries
 *
 * SR refs: SR-01, SR-02, SR-05, SR-06, SR-13, SR-16, SR-20
 * Feature refs: F-09, F-13, F-14, F-15, F-16, F-22
 */
return new class extends Migration {
    public function up(): void
    {
        // ── 1. Chart of Accounts ──────────────────────────────────────────
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->string('code')->primary();
            $table->string('name');
            $table->string('type');           // Asset | Liability | Equity | Income | Expense
            $table->string('normal_balance'); // Debit | Credit
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── 2. Invoices (Customer Invoice & Vendor Bill) ──────────────────
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('document_number')->unique();
            $table->string('invoice_type');    // customer_invoice | vendor_bill | credit_note
            $table->string('status')->default('draft'); // draft | posted | paid | cancelled

            $table->string('partner_id');
            $table->string('partner_name');
            $table->string('partner_type');    // customer | vendor

            $table->string('source_document_id')->nullable();
            $table->string('source_document_type')->nullable();

            $table->unsignedBigInteger('original_invoice_id')->nullable();
            $table->foreign('original_invoice_id')->references('id')->on('invoices')->nullOnDelete();

            $table->unsignedBigInteger('untaxed_amount')->default(0);
            $table->unsignedBigInteger('tax_amount')->default(0);
            $table->unsignedBigInteger('gross_amount')->default(0);
            $table->unsignedBigInteger('amount_paid')->default(0);
            $table->unsignedBigInteger('amount_outstanding')->default(0);

            $table->string('tax_code')->default('PPN-NONLUX');
            $table->decimal('tax_rate', 5, 4)->default(0.1100);

            $table->string('payment_terms')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();

            $table->string('created_by');
            $table->string('posted_by')->nullable();
            $table->timestamp('posted_at')->nullable();

            $table->timestamps();
        });

        // ── 3. Invoice Items ──────────────────────────────────────────────
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();

            $table->string('product_id');
            $table->string('variant_sku');
            $table->string('product_name');
            $table->string('unit');

            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price');
            $table->decimal('discount_percent', 5, 2)->default(0.00);

            $table->unsignedBigInteger('net_amount');
            $table->unsignedBigInteger('tax_amount');
            $table->unsignedBigInteger('gross_amount');

            $table->string('delivery_ref')->nullable();
            $table->unsignedInteger('delivered_qty')->default(0);
            $table->unsignedInteger('accepted_qty')->default(0);
            $table->unsignedInteger('invoiced_qty')->default(0);

            $table->timestamps();
        });

        // ── 4. Journal Entries ────────────────────────────────────────────
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();

            $table->string('journal_type');  // sales | purchase | payment | stock | opening
            $table->string('reference');

            $table->string('idempotency_key')->unique(); // SR-16: prevents double posting

            $table->string('debit_account');
            $table->string('credit_account');
            $table->unsignedBigInteger('amount');

            $table->text('description')->nullable();
            $table->string('posted_by');
            $table->timestamp('posted_at')->useCurrent();

            $table->boolean('is_reversed')->default(false);
            $table->unsignedBigInteger('reversed_by_entry_id')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('chart_of_accounts');
    }
};
