<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

/**
 * ╔══════════════════════════════════════════════════════════════════════════╗
 * ║  RunFmcgMasterSeed — PT Arunika Distribusi Cepat                        ║
 * ║  Single-file DDL + DML seed for the complete Handout §4 baseline.       ║
 * ║                                                                          ║
 * ║  Usage:  php artisan db:master-seed                                      ║
 * ║          php artisan db:master-seed --fresh   (drop ALL tables first)    ║
 * ║                                                                          ║
 * ║  What this does                                                          ║
 * ║  ─────────────                                                           ║
 * ║  1. Drops (if they exist) every table this ERP owns, in safe FK order.  ║
 * ║  2. Re-creates all 14 tables with exact Handout §4 column names.         ║
 * ║  3. Inserts every master-data row required by §4.                        ║
 * ║                                                                          ║
 * ║  Column naming note for opening_stock                                    ║
 * ║  ────────────────────────────────────                                    ║
 * ║  Handout §4 lists columns "WH-JKT_on_hand" and "HUB-BDG_on_hand".       ║
 * ║  SQL identifiers cannot contain hyphens unquoted, so this file uses      ║
 * ║  wh_jkt_on_hand / hub_bdg_on_hand — identical semantics, safe names.    ║
 * ╚══════════════════════════════════════════════════════════════════════════╝
 */
class RunFmcgMasterSeed extends Command
{
    protected $signature   = 'db:master-seed {--fresh : Drop and recreate ALL tables before seeding}';
    protected $description = 'Drop, recreate and fully seed all Handout §4 tables for PT Arunika ERP';

    // ────────────────────────────────────────────────────────────────────────
    //  Entry point
    // ────────────────────────────────────────────────────────────────────────
    public function handle(): int
    {
        $this->info('');
        $this->info('╔══════════════════════════════════════════════════╗');
        $this->info('║  PT Arunika ERP — Master Seed (Handout §4)       ║');
        $this->info('╚══════════════════════════════════════════════════╝');
        $this->info('');

        // SQLite: disable FK checks during structural changes
        DB::statement('PRAGMA foreign_keys = OFF');

        $this->step('1/3', 'Dropping existing tables …');
        $this->dropAllTables();

        $this->step('2/3', 'Creating schema …');
        $this->createSchema();

        // Re-enable FK enforcement before inserting data
        DB::statement('PRAGMA foreign_keys = ON');

        $this->step('3/3', 'Seeding master data …');
        $this->seedAll();

        $this->info('');
        $this->info('✔  Done. Database is fully populated and ready for testing.');
        $this->info('');
        return self::SUCCESS;
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SECTION 1 — DROP TABLES (reverse FK dependency order)
    // ═══════════════════════════════════════════════════════════════════════
    private function dropAllTables(): void
    {
        // Application tables only — innermost FK dependents first.
        // We do NOT touch Laravel framework tables (migrations, failed_jobs,
        // password_reset_tokens, personal_access_tokens) because the Artisan
        // bootstrapper owns those and recreating them causes conflicts.
        $tables = [
            'audit_logs',
            'delivery_lines',
            'deliveries',
            'receipt_lines',
            'receipts',
            'purchase_order_lines',
            'purchase_orders',
            'stock_moves',
            'sales_order_lines',
            'sales_orders',
            'leads',
            'journal_entries',
            'invoice_items',
            'invoices',
            'chart_of_accounts',
            'product_lots',
            'opening_stock',
            'vendor_pricelist',
            'products',
            'warehouses_locations',
            'contacts_vendors',
            'contacts_customers',
            'employee_private_records',
            'employees',
            'users',
            'departments',
            'tax_rates',
            'payment_terms',
        ];

        foreach ($tables as $t) {
            Schema::dropIfExists($t);
            $this->line("   dropped  {$t}");
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SECTION 2 — CREATE SCHEMA
    // ═══════════════════════════════════════════════════════════════════════
    private function createSchema(): void
    {
        // NOTE: We do NOT recreate Laravel framework tables here.
        // migrations, failed_jobs, password_reset_tokens, personal_access_tokens
        // are managed by Artisan's own bootstrap and migration runner.

        // ── tax_rates ─────────────────────────────────────────────────────
        // Handout §4: PPN 12% statutory / 11% effective (11/12 tax base factor)
        Schema::create('tax_rates', function (Blueprint $t) {
            $t->string('code')->primary();       // PPN-NONLUX
            $t->string('name');
            $t->decimal('rate', 5, 4);           // 0.1200 — statutory 12%
            $t->decimal('tax_base_factor', 6, 4);// 0.9167 — 11/12 multiplier
            $t->string('rounding')->default('per_line'); // per_line | per_invoice
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        $this->line('   created  tax_rates');

        // ── payment_terms ─────────────────────────────────────────────────
        // Handout §4: CASH (0 days), NET14 (14 days), NET30 (30 days)
        Schema::create('payment_terms', function (Blueprint $t) {
            $t->string('code')->primary();       // CASH | NET14 | NET30
            $t->string('name');                  // "Tunai" | "14 Hari" | "30 Hari"
            $t->unsignedSmallInteger('days');    // 0 | 14 | 30
            $t->string('description')->nullable();
            $t->timestamps();
        });
        $this->line('   created  payment_terms');

        // ── users ─────────────────────────────────────────────────────────
        // Columns: id, name, username, email, password, role
        Schema::create('users', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('username')->unique()->nullable();
            $t->string('email')->unique();
            $t->timestamp('email_verified_at')->nullable();
            $t->string('password');
            $t->string('role')->default('Sales');
            $t->rememberToken();
            $t->timestamps();
        });
        $this->line('   created  users');

        // ── departments ───────────────────────────────────────────────────
        // Columns: dept_id, name, manager_employee, company
        Schema::create('departments', function (Blueprint $t) {
            $t->string('dept_id')->primary();
            $t->string('name');
            $t->string('manager_employee')->nullable();
            $t->string('company')->default('Arunika');
            $t->timestamps();
        });
        $this->line('   created  departments');

        // ── employees ────────────────────────────────────────────────────
        // Columns: id, name, department_id, job_title, location, status, mapped_user_id
        Schema::create('employees', function (Blueprint $t) {
            $t->string('id')->primary();
            $t->string('name');
            $t->string('department_id');
            $t->string('job_title');
            $t->string('location');
            $t->string('status')->default('Aktif'); // Aktif | Nonaktif
            $t->unsignedBigInteger('mapped_user_id')->nullable();
            $t->foreign('mapped_user_id')->references('id')->on('users')->nullOnDelete();
            $t->foreign('department_id')->references('dept_id')->on('departments');
            $t->timestamps();
        });
        $this->line('   created  employees');

        // ── employee_private_records ──────────────────────────────────────
        // Columns: employee_id, personal_email, emergency_contact, private_phone
        Schema::create('employee_private_records', function (Blueprint $t) {
            $t->id();
            $t->string('employee_id');
            $t->string('personal_email');
            $t->string('emergency_contact');
            $t->string('private_phone');
            $t->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            $t->timestamps();
        });
        $this->line('   created  employee_private_records');

        // ── chart_of_accounts ─────────────────────────────────────────────
        // Columns: code, name, type, normal_balance, is_active
        Schema::create('chart_of_accounts', function (Blueprint $t) {
            $t->string('code')->primary();
            $t->string('name');
            $t->string('type');            // Asset | Liability | Equity | Income | Expense
            $t->string('normal_balance'); // Debit | Credit
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        $this->line('   created  chart_of_accounts');

        // ── invoices ──────────────────────────────────────────────────────
        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->string('document_number')->unique();
            $t->string('invoice_type');   // customer_invoice | vendor_bill | credit_note
            $t->string('status')->default('draft');
            $t->string('partner_id');
            $t->string('partner_name');
            $t->string('partner_type');   // customer | vendor
            $t->string('source_document_id')->nullable();
            $t->string('source_document_type')->nullable();
            $t->unsignedBigInteger('original_invoice_id')->nullable();
            $t->foreign('original_invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $t->unsignedBigInteger('untaxed_amount')->default(0);
            $t->unsignedBigInteger('tax_amount')->default(0);
            $t->unsignedBigInteger('gross_amount')->default(0);
            $t->unsignedBigInteger('amount_paid')->default(0);
            $t->unsignedBigInteger('amount_outstanding')->default(0);
            $t->string('tax_code')->default('PPN-NONLUX');
            $t->decimal('tax_rate', 5, 4)->default(0.1100);
            $t->string('payment_terms')->nullable();
            $t->date('invoice_date')->nullable();
            $t->date('due_date')->nullable();
            $t->string('created_by');
            $t->string('posted_by')->nullable();
            $t->timestamp('posted_at')->nullable();
            $t->timestamps();
        });
        $this->line('   created  invoices');

        // ── invoice_items ─────────────────────────────────────────────────
        Schema::create('invoice_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('invoice_id');
            $t->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $t->string('product_id');
            $t->string('variant_sku');
            $t->string('product_name');
            $t->string('unit');
            $t->unsignedInteger('quantity');
            $t->unsignedBigInteger('unit_price');
            $t->decimal('discount_percent', 5, 2)->default(0.00);
            $t->unsignedBigInteger('net_amount');
            $t->unsignedBigInteger('tax_amount');
            $t->unsignedBigInteger('gross_amount');
            $t->string('delivery_ref')->nullable();
            $t->unsignedInteger('delivered_qty')->default(0);
            $t->unsignedInteger('accepted_qty')->default(0);
            $t->unsignedInteger('invoiced_qty')->default(0);
            $t->timestamps();
        });
        $this->line('   created  invoice_items');

        // ── journal_entries ───────────────────────────────────────────────
        // Columns: id, invoice_id, journal_type, reference, idempotency_key,
        //          debit_account, credit_account, amount, description,
        //          posted_by, posted_at, is_reversed
        Schema::create('journal_entries', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('invoice_id')->nullable();
            $t->foreign('invoice_id')->references('id')->on('invoices')->nullOnDelete();
            $t->string('journal_type');  // sales | purchase | payment | stock | opening
            $t->string('reference');
            $t->string('idempotency_key')->unique(); // SR-16: prevents double-posting
            $t->string('debit_account');
            $t->string('credit_account');
            $t->unsignedBigInteger('amount');
            $t->text('description')->nullable();
            $t->string('posted_by');
            $t->timestamp('posted_at')->useCurrent();
            $t->boolean('is_reversed')->default(false);
            $t->unsignedBigInteger('reversed_by_entry_id')->nullable();
            $t->timestamps();
        });
        $this->line('   created  journal_entries');

        // ── contacts_customers ────────────────────────────────────────────
        // Columns: id, name, type, city, credit_limit, payment_terms, open_ar
        Schema::create('contacts_customers', function (Blueprint $t) {
            $t->string('id')->primary();  // C01 … C06
            $t->string('name');
            $t->string('type')->default('B2B');
            $t->string('city');
            $t->unsignedBigInteger('credit_limit')->default(0);
            $t->string('payment_terms');
            $t->unsignedBigInteger('open_ar')->default(0);
            $t->timestamps();
        });
        $this->line('   created  contacts_customers');

        // ── contacts_vendors ──────────────────────────────────────────────
        // Columns: id, name, city, payment_terms, category
        Schema::create('contacts_vendors', function (Blueprint $t) {
            $t->string('id')->primary();  // V01 … V04
            $t->string('name');
            $t->string('city');
            $t->string('payment_terms');
            $t->string('category');
            $t->timestamps();
        });
        $this->line('   created  contacts_vendors');

        // ── products ──────────────────────────────────────────────────────
        // Columns: id, variant_sku, name, size, color, unit, sales_price, cost, default_vendor
        Schema::create('products', function (Blueprint $t) {
            $t->string('id')->primary();  // P01 … P16
            $t->string('variant_sku')->unique();
            $t->string('name');
            $t->string('size');
            $t->string('color');
            $t->string('unit');
            $t->unsignedBigInteger('sales_price');
            $t->unsignedBigInteger('cost');
            $t->string('default_vendor');
            $t->foreign('default_vendor')->references('id')->on('contacts_vendors');
            $t->timestamps();
        });
        $this->line('   created  products');

        // ── vendor_pricelist ──────────────────────────────────────────────
        // Columns: vendor_id, variant_sku, unit, unit_price, min_qty
        Schema::create('vendor_pricelist', function (Blueprint $t) {
            $t->id();
            $t->string('vendor_id');
            $t->string('variant_sku');
            $t->string('unit');
            $t->unsignedBigInteger('unit_price');
            $t->unsignedInteger('min_qty')->default(1);
            $t->foreign('vendor_id')->references('id')->on('contacts_vendors');
            $t->foreign('variant_sku')->references('variant_sku')->on('products');
            $t->unique(['vendor_id', 'variant_sku']);
            $t->timestamps();
        });
        $this->line('   created  vendor_pricelist');

        // ── warehouses_locations ──────────────────────────────────────────
        // Columns: code, name, city, usage
        Schema::create('warehouses_locations', function (Blueprint $t) {
            $t->string('code')->primary();  // WH-JKT | HUB-BDG
            $t->string('name');
            $t->string('city');
            $t->string('usage')->default('Internal');
            $t->timestamps();
        });
        $this->line('   created  warehouses_locations');

        // ── opening_stock ─────────────────────────────────────────────────
        // Handout §4 logical columns: variant_sku, WH-JKT_on_hand, HUB-BDG_on_hand,
        //   company_total, standard_cost, standard_value
        // SQL-safe names used: wh_jkt_on_hand, hub_bdg_on_hand
        // company_total is a stored denorm; standard_value = company_total × standard_cost
        Schema::create('opening_stock', function (Blueprint $t) {
            $t->id();
            $t->string('variant_sku')->unique();
            $t->unsignedInteger('wh_jkt_on_hand')->default(0);   // WH-JKT_on_hand
            $t->unsignedInteger('hub_bdg_on_hand')->default(0);  // HUB-BDG_on_hand
            $t->unsignedInteger('company_total')->default(0);
            $t->unsignedInteger('reserved')->default(0);
            $t->unsignedBigInteger('standard_cost');
            $t->unsignedBigInteger('standard_value');
            $t->foreign('variant_sku')->references('variant_sku')->on('products');
            $t->timestamps();
        });
        $this->line('   created  opening_stock');

        // ── product_lots ──────────────────────────────────────────────────
        // Columns: lot_id, variant_sku, location, qty, expiry_date, selection_policy
        Schema::create('product_lots', function (Blueprint $t) {
            $t->string('lot_id')->primary();  // LOT-TEA-01 …
            $t->string('variant_sku');
            $t->string('location');
            $t->unsignedInteger('qty')->default(0);
            $t->date('expiry_date');
            $t->string('selection_policy')->default('FEFO');
            $t->foreign('variant_sku')->references('variant_sku')->on('products');
            $t->foreign('location')->references('code')->on('warehouses_locations');
            $t->timestamps();
        });
        $this->line('   created  product_lots');

        // ── audit_logs ────────────────────────────────────────────────────
        // Columns: id, document_type, document_id, action, actor_user,
        //          actor_employee, description, old_values, new_values, acted_at
        Schema::create('audit_logs', function (Blueprint $t) {
            $t->id();
            $t->string('document_type');
            $t->string('document_id');
            $t->string('action');
            $t->string('actor_user');
            $t->string('actor_employee')->nullable();
            $t->text('description')->nullable();
            $t->json('old_values')->nullable();
            $t->json('new_values')->nullable();
            $t->timestamp('acted_at')->useCurrent();
            $t->timestamps();
        });
        $this->line('   created  audit_logs');

        // ── leads (CRM — F-04, SR-19) ───────────────────────────────────
        Schema::create('leads', function (Blueprint $t) {
            $t->string('id')->primary();        // L00001 …
            $t->string('partner_id')->nullable();
            $t->string('partner_name')->nullable();
            $t->string('stage');                // new | qualified | won | lost
            $t->string('salesperson_id')->nullable();
            $t->string('salesperson_name')->nullable();
            $t->unsignedBigInteger('expected_revenue')->default(0);
            $t->text('notes')->nullable();
            $t->string('created_by')->nullable();
            $t->timestamps();
        });
        $this->line('   created  leads');

        // ── sales_orders (F-05, F-06, F-10, SR-11, SR-12) ────────────────
        Schema::create('sales_orders', function (Blueprint $t) {
            $t->string('id')->primary();        // S00007, S00008 …
            $t->string('customer_id');
            $t->foreign('customer_id')->references('id')->on('contacts_customers');
            $t->string('customer_name');
            $t->string('status')->default('draft');
            // Discount approval workflow (SR-07/08)
            $t->string('discount_approval_status')->default('not_required');
            $t->string('discount_approved_by')->nullable();
            $t->timestamp('discount_approved_at')->nullable();
            $t->unsignedBigInteger('untaxed_amount')->default(0);
            $t->unsignedBigInteger('tax_amount')->default(0);
            $t->unsignedBigInteger('gross_amount')->default(0);
            $t->string('payment_terms')->nullable();
            $t->date('order_date')->nullable();
            $t->date('delivery_promise_date')->nullable();  // SR-03: max 3-day promise
            $t->string('created_by')->nullable();
            $t->string('confirmed_by')->nullable();
            $t->timestamp('confirmed_at')->nullable();
            $t->string('lead_id')->nullable();
            $t->timestamps();
        });
        $this->line('   created  sales_orders');

        // ── sales_order_lines (F-05, SR-12 price lock, SR-22 reservation) ─
        Schema::create('sales_order_lines', function (Blueprint $t) {
            $t->id();
            $t->string('sales_order_id');
            $t->foreign('sales_order_id')->references('id')->on('sales_orders')->cascadeOnDelete();
            $t->string('product_id');
            $t->string('variant_sku');
            $t->string('product_name');
            $t->string('unit');
            $t->unsignedInteger('quantity');
            $t->unsignedBigInteger('unit_price');           // locked on confirm (SR-12)
            $t->decimal('discount_percent', 5, 2)->default(0.00);
            $t->boolean('is_locked')->default(false);       // SR-12: true after confirm
            $t->unsignedBigInteger('net_amount');
            $t->unsignedBigInteger('tax_amount');
            $t->unsignedBigInteger('gross_amount');
            // Delivery tracking (for invoicing SR-02)
            $t->unsignedInteger('delivered_qty')->default(0);
            $t->unsignedInteger('invoiced_qty')->default(0);
            // Stock reservation (SR-22)
            $t->unsignedInteger('reserved_qty')->default(0);
            $t->string('source_location')->default('WH-JKT');
            $t->timestamps();
        });
        $this->line('   created  sales_order_lines');

        // ── purchase_orders ──────────────────────────────────────────────
        Schema::create('purchase_orders', function (Blueprint $t) {
            $t->string('id')->primary();           // PO00001 ...
            $t->string('vendor_id');
            $t->foreign('vendor_id')->references('id')->on('contacts_vendors');
            $t->string('vendor_name');
            $t->string('status')->default('rfq'); // rfq | confirmed | done | cancelled
            $t->boolean('is_locked')->default(false); // SR-21: true after confirmed
            $t->unsignedBigInteger('net_amount')->default(0);
            $t->unsignedBigInteger('tax_amount')->default(0);
            $t->unsignedBigInteger('gross_amount')->default(0);
            $t->string('payment_terms')->nullable();
            $t->date('order_date')->nullable();
            $t->date('expected_arrival')->nullable();
            $t->string('created_by')->nullable();
            $t->string('confirmed_by')->nullable();
            $t->timestamp('confirmed_at')->nullable();
            $t->timestamps();
        });
        $this->line('   created  purchase_orders');

        // ── purchase_order_lines ──────────────────────────────────────────
        Schema::create('purchase_order_lines', function (Blueprint $t) {
            $t->id();
            $t->string('purchase_order_id');
            $t->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            $t->string('product_id');
            $t->string('variant_sku');
            $t->string('product_name');
            $t->string('unit');
            $t->unsignedInteger('quantity');
            $t->unsignedBigInteger('unit_price');     // locked on confirm (SR-21)
            $t->boolean('is_locked')->default(false); // SR-21: true after PO confirmed
            $t->unsignedBigInteger('net_amount');
            $t->unsignedBigInteger('tax_amount');
            $t->unsignedBigInteger('gross_amount');
            $t->unsignedInteger('received_qty')->default(0);
            $t->unsignedInteger('accepted_qty')->default(0);
            $t->unsignedInteger('rejected_qty')->default(0);
            $t->unsignedInteger('billed_qty')->default(0);
            $t->timestamps();
        });
        $this->line('   created  purchase_order_lines');

        // ── receipts (Goods Receipt) ──────────────────────────────────────
        Schema::create('receipts', function (Blueprint $t) {
            $t->string('id')->primary();           // GR00001 ...
            $t->string('purchase_order_id');
            $t->foreign('purchase_order_id')->references('id')->on('purchase_orders');
            $t->string('vendor_id');
            $t->string('status')->default('draft'); // draft | done | cancelled
            $t->string('destination_location')->default('WH-JKT');
            $t->string('validator_employee_id')->nullable(); // SR-17
            $t->string('validator_user')->nullable();
            $t->timestamp('validated_at')->nullable();
            $t->string('created_by')->nullable();
            $t->boolean('is_posted')->default(false); // SR-16 idempotency
            $t->string('idempotency_key')->unique()->nullable();
            $t->timestamps();
        });
        $this->line('   created  receipts');

        // ── receipt_lines ─────────────────────────────────────────────────
        Schema::create('receipt_lines', function (Blueprint $t) {
            $t->id();
            $t->string('receipt_id');
            $t->foreign('receipt_id')->references('id')->on('receipts')->cascadeOnDelete();
            $t->string('purchase_order_line_id')->nullable();
            $t->string('product_id');
            $t->string('variant_sku');
            $t->string('product_name');
            $t->string('unit');
            $t->unsignedInteger('ordered_qty')->default(0);
            $t->unsignedInteger('received_qty')->default(0);
            $t->unsignedInteger('accepted_qty')->default(0);  // SR-06: only this goes to stock
            $t->unsignedInteger('rejected_qty')->default(0);
            $t->string('lot_id')->nullable();                  // for lot-tracked SKUs
            $t->date('expiry_date')->nullable();
            $t->timestamps();
        });
        $this->line('   created  receipt_lines');

        // ── deliveries (Delivery Order) ───────────────────────────────────
        Schema::create('deliveries', function (Blueprint $t) {
            $t->string('id')->primary();            // DO00001 ...
            $t->string('sales_order_id');
            $t->foreign('sales_order_id')->references('id')->on('sales_orders');
            $t->string('customer_id');
            $t->string('status')->default('draft'); // draft | ready | done | cancelled
            $t->string('source_location')->default('WH-JKT');
            $t->string('validator_employee_id')->nullable(); // SR-17
            $t->string('validator_user')->nullable();
            $t->timestamp('validated_at')->nullable();
            $t->string('created_by')->nullable();
            $t->boolean('is_posted')->default(false); // SR-16 idempotency
            $t->string('idempotency_key')->unique()->nullable();
            $t->timestamps();
        });
        $this->line('   created  deliveries');

        // ── delivery_lines ────────────────────────────────────────────────
        Schema::create('delivery_lines', function (Blueprint $t) {
            $t->id();
            $t->string('delivery_id');
            $t->foreign('delivery_id')->references('id')->on('deliveries')->cascadeOnDelete();
            $t->string('sales_order_line_id')->nullable();
            $t->string('product_id');
            $t->string('variant_sku');
            $t->string('product_name');
            $t->string('unit');
            $t->unsignedInteger('demand_qty');
            $t->unsignedInteger('done_qty')->default(0);
            $t->unsignedInteger('backorder_qty')->default(0);
            $t->string('lot_id')->nullable();         // SR-09: FEFO lot picked
            $t->timestamps();
        });
        $this->line('   created  delivery_lines');

        // ── stock_moves (perpetual ledger) ────────────────────────────────
        Schema::create('stock_moves', function (Blueprint $t) {
            $t->id();
            $t->string('reference');               // DO00001, GR00001, TRANSFER-001 ...
            $t->string('move_type');               // delivery | receipt | transfer | return | adjustment
            $t->string('variant_sku');
            $t->string('from_location')->nullable(); // null for receipts
            $t->string('to_location')->nullable();   // null for deliveries
            $t->unsignedInteger('qty');
            $t->unsignedBigInteger('unit_cost');   // standard_cost at time of move (SR-03)
            $t->unsignedBigInteger('total_value'); // qty × unit_cost
            $t->string('lot_id')->nullable();      // FEFO: which lot was moved
            $t->string('actor_user');
            $t->string('actor_employee')->nullable();
            $t->string('idempotency_key')->unique(); // SR-16: prevent double-posting
            $t->timestamps();
        });
        $this->line('   created  stock_moves');
    }

    // ═══════════════════════════════════════════════════════════════════════
    //  SECTION 3 — SEED ALL DATA
    // ═══════════════════════════════════════════════════════════════════════
    private function seedAll(): void
    {
        $now = now();

        // ── 3.1  Tax Rates ────────────────────────────────────────────────
        // Handout §4: Indonesia VAT — statutory 12%, effective 11% via 11/12 base factor.
        // tax_amount per line = FLOOR( net × (11/12) × 0.12 + 0.5 ) = FLOOR( net × 0.11 + 0.5 )
        DB::table('tax_rates')->insert([
            [
                'code'            => 'PPN-NONLUX',
                'name'            => 'PPN Barang Kena Pajak Non-Mewah',
                'rate'            => 0.1200,
                'tax_base_factor' => 0.9167,   // 11/12 ≈ 0.91666…, rounded to 4 dp
                'rounding'        => 'per_line',
                'is_active'       => 1,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ]);
        $this->line('   seeded   tax_rates (1 row — PPN-NONLUX 12% statutory / 11% effective)');

        // ── 3.2  Payment Terms ────────────────────────────────────────────
        // Handout §4: three settlement terms used across customers and vendors.
        // "name" matches the human-readable label stored in contacts_customers.payment_terms.
        DB::table('payment_terms')->insert([
            ['code' => 'CASH',  'name' => 'Tunai',   'days' => 0,  'description' => 'Pembayaran tunai, tidak ada kredit.',              'created_at' => $now, 'updated_at' => $now],
            ['code' => 'NET14', 'name' => '14 Hari', 'days' => 14, 'description' => 'Jatuh tempo 14 hari kalender setelah tanggal faktur.', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'NET30', 'name' => '30 Hari', 'days' => 30, 'description' => 'Jatuh tempo 30 hari kalender setelah tanggal faktur.', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->line('   seeded   payment_terms (3 rows — CASH / NET14 / NET30)');

        // ── 3.3  Chart of Accounts ────────────────────────────────────────
        // Handout §4, COA table. 4100/4200 carry Debit normal balance
        // because they are contra-income (returns & discounts).
        DB::table('chart_of_accounts')->insert([
            ['code' => '1000', 'name' => 'Kas dan Bank',                     'type' => 'Asset',     'normal_balance' => 'Debit',  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '1100', 'name' => 'Piutang Usaha',                    'type' => 'Asset',     'normal_balance' => 'Debit',  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '1300', 'name' => 'Persediaan Barang',                'type' => 'Asset',     'normal_balance' => 'Debit',  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '1350', 'name' => 'Stock Interim / Receipt Clearing', 'type' => 'Asset',     'normal_balance' => 'Debit',  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '1400', 'name' => 'PPN Masukan',                      'type' => 'Asset',     'normal_balance' => 'Debit',  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '1500', 'name' => 'Aset Tetap',                       'type' => 'Asset',     'normal_balance' => 'Debit',  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '2000', 'name' => 'Utang Usaha',                      'type' => 'Liability', 'normal_balance' => 'Credit', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '2100', 'name' => 'PPN Keluaran',                     'type' => 'Liability', 'normal_balance' => 'Credit', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '2200', 'name' => 'Utang Biaya',                      'type' => 'Liability', 'normal_balance' => 'Credit', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '3000', 'name' => 'Modal / Saldo Awal',               'type' => 'Equity',    'normal_balance' => 'Credit', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '4000', 'name' => 'Penjualan',                        'type' => 'Income',    'normal_balance' => 'Credit', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '4100', 'name' => 'Retur Penjualan',                  'type' => 'Income',    'normal_balance' => 'Debit',  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '4200', 'name' => 'Diskon Penjualan',                 'type' => 'Income',    'normal_balance' => 'Debit',  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '5100', 'name' => 'HPP',                              'type' => 'Expense',   'normal_balance' => 'Debit',  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['code' => '6100', 'name' => 'Beban Operasional',                'type' => 'Expense',   'normal_balance' => 'Debit',  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->line('   seeded   chart_of_accounts (15 rows)');

        // ── 3.2  Opening Balance Journal Entries ──────────────────────────
        //
        //  Debit  1000  Kas dan Bank            Rp 20,000,000
        //  Debit  1100  Piutang Usaha           Rp  1,500,000   (C01 900k + C02 600k)
        //  Debit  1300  Persediaan Barang       Rp 23,095,000   (WH-JKT 17,315k + HUB-BDG 5,780k)
        //  Credit 2000  Utang Usaha             Rp    700,000   (V04 outstanding AP)
        //  Credit 3000  Modal / Saldo Awal      Rp 43,895,000
        //
        //  Total Debit  = 20,000,000 + 1,500,000 + 23,095,000 = 44,595,000
        //  Total Credit =    700,000 + 43,895,000              = 44,595,000  ✓ BALANCED
        //
        DB::table('journal_entries')->insert([
            [
                'invoice_id'            => null,
                'journal_type'          => 'opening',
                'reference'             => 'OPENING-BALANCE',
                'idempotency_key'       => md5('opening|OPENING-BALANCE|1000|3000|20000000'),
                'debit_account'         => '1000',
                'credit_account'        => '3000',
                'amount'                => 20_000_000,
                'description'           => 'Opening Balance: Kas dan Bank Rp20.000.000',
                'posted_by'             => 'system',
                'posted_at'             => $now,
                'is_reversed'           => 0,
                'reversed_by_entry_id'  => null,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
            [
                'invoice_id'            => null,
                'journal_type'          => 'opening',
                'reference'             => 'OPENING-BALANCE',
                'idempotency_key'       => md5('opening|OPENING-BALANCE|1100|3000|1500000'),
                'debit_account'         => '1100',
                'credit_account'        => '3000',
                'amount'                => 1_500_000,
                'description'           => 'Opening Balance: Piutang Usaha Rp1.500.000 (C01 Rp900.000 + C02 Rp600.000)',
                'posted_by'             => 'system',
                'posted_at'             => $now,
                'is_reversed'           => 0,
                'reversed_by_entry_id'  => null,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
            [
                'invoice_id'            => null,
                'journal_type'          => 'opening',
                'reference'             => 'OPENING-BALANCE',
                'idempotency_key'       => md5('opening|OPENING-BALANCE|1300|3000|23095000'),
                'debit_account'         => '1300',
                'credit_account'        => '3000',
                'amount'                => 23_095_000,
                'description'           => 'Opening Balance: Persediaan Barang Rp23.095.000 (WH-JKT Rp17.315.000 + HUB-BDG Rp5.780.000)',
                'posted_by'             => 'system',
                'posted_at'             => $now,
                'is_reversed'           => 0,
                'reversed_by_entry_id'  => null,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
            [
                'invoice_id'            => null,
                'journal_type'          => 'opening',
                'reference'             => 'OPENING-BALANCE',
                'idempotency_key'       => md5('opening|OPENING-BALANCE|3000|2000|700000'),
                'debit_account'         => '3000',
                'credit_account'        => '2000',
                'amount'                => 700_000,
                'description'           => 'Opening Balance: Utang Usaha AP Rp700.000 (V04 PT Personal Sentosa)',
                'posted_by'             => 'system',
                'posted_at'             => $now,
                'is_reversed'           => 0,
                'reversed_by_entry_id'  => null,
                'created_at'            => $now,
                'updated_at'            => $now,
            ],
        ]);
        $this->line('   seeded   journal_entries (4 opening balance rows — Rp44.595.000 balanced)');

        // ── 3.3  Departments ──────────────────────────────────────────────
        DB::table('departments')->insert([
            ['dept_id' => 'D-WH',  'name' => 'Warehouse',       'manager_employee' => 'E01', 'company' => 'Arunika', 'created_at' => $now, 'updated_at' => $now],
            ['dept_id' => 'D-SAL', 'name' => 'Sales',           'manager_employee' => 'E04', 'company' => 'Arunika', 'created_at' => $now, 'updated_at' => $now],
            ['dept_id' => 'D-FIN', 'name' => 'Finance',         'manager_employee' => 'E06', 'company' => 'Arunika', 'created_at' => $now, 'updated_at' => $now],
            ['dept_id' => 'D-PUR', 'name' => 'Procurement',     'manager_employee' => 'E07', 'company' => 'Arunika', 'created_at' => $now, 'updated_at' => $now],
            ['dept_id' => 'D-HR',  'name' => 'Human Resources', 'manager_employee' => 'E08', 'company' => 'Arunika', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->line('   seeded   departments (5 rows)');

        // ── 3.4  Users ────────────────────────────────────────────────────
        // All passwords: Demo2026!
        $pw = Hash::make('Demo2026!');
        DB::table('users')->insert([
            ['id' => 1, 'name' => 'Sari Prameswari', 'username' => 'warehouse_mgr',  'email' => 'warehouse_mgr@arunika.test',  'password' => $pw, 'role' => 'Warehouse', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'Arif Maulana',    'username' => 'warehouse_user', 'email' => 'warehouse_user@arunika.test', 'password' => $pw, 'role' => 'Warehouse', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'Dini Pratiwi',    'username' => 'sales_user',     'email' => 'sales_user@arunika.test',     'password' => $pw, 'role' => 'Sales',     'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'Farhan Saputra',  'username' => 'sales_user2',    'email' => 'sales_user2@arunika.test',    'password' => $pw, 'role' => 'Sales',     'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'Made Wirawan',    'username' => 'finance_user',   'email' => 'finance_user@arunika.test',   'password' => $pw, 'role' => 'Finance',   'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'name' => 'Rita Kusuma',     'username' => 'purchase_user',  'email' => 'purchase_user@arunika.test',  'password' => $pw, 'role' => 'Purchase',  'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'name' => 'Melati Yusuf',    'username' => 'hr_user',        'email' => 'hr_user@arunika.test',        'password' => $pw, 'role' => 'HR',        'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'name' => 'Hendra Widodo',   'username' => 'owner_user',     'email' => 'owner_user@arunika.test',     'password' => $pw, 'role' => 'Owner',     'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->line('   seeded   users (8 rows — password: Demo2026!)');

        // ── 3.5  Employees ────────────────────────────────────────────────
        DB::table('employees')->insert([
            ['id' => 'E01', 'name' => 'Sari Prameswari', 'department_id' => 'D-WH',  'job_title' => 'Supervisor Gudang',   'location' => 'WH-JKT', 'status' => 'Aktif',    'mapped_user_id' => 1,    'created_at' => $now, 'updated_at' => $now],
            ['id' => 'E02', 'name' => 'Arif Maulana',    'department_id' => 'D-WH',  'job_title' => 'Petugas Gudang',      'location' => 'WH-JKT', 'status' => 'Aktif',    'mapped_user_id' => 2,    'created_at' => $now, 'updated_at' => $now],
            ['id' => 'E03', 'name' => 'Naya Putri',      'department_id' => 'D-WH',  'job_title' => 'Petugas Gudang',      'location' => 'WH-JKT', 'status' => 'Nonaktif', 'mapped_user_id' => null, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'E04', 'name' => 'Dini Pratiwi',    'department_id' => 'D-SAL', 'job_title' => 'Sales Lead',          'location' => 'JKT',    'status' => 'Aktif',    'mapped_user_id' => 3,    'created_at' => $now, 'updated_at' => $now],
            ['id' => 'E05', 'name' => 'Farhan Saputra',  'department_id' => 'D-SAL', 'job_title' => 'Sales Executive',     'location' => 'JKT',    'status' => 'Aktif',    'mapped_user_id' => 4,    'created_at' => $now, 'updated_at' => $now],
            ['id' => 'E06', 'name' => 'Made Wirawan',    'department_id' => 'D-FIN', 'job_title' => 'Finance Officer',     'location' => 'JKT',    'status' => 'Aktif',    'mapped_user_id' => 5,    'created_at' => $now, 'updated_at' => $now],
            ['id' => 'E07', 'name' => 'Rita Kusuma',     'department_id' => 'D-PUR', 'job_title' => 'Procurement Officer', 'location' => 'JKT',    'status' => 'Aktif',    'mapped_user_id' => 6,    'created_at' => $now, 'updated_at' => $now],
            ['id' => 'E08', 'name' => 'Melati Yusuf',    'department_id' => 'D-HR',  'job_title' => 'HR Administrator',    'location' => 'JKT',    'status' => 'Aktif',    'mapped_user_id' => 7,    'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->line('   seeded   employees (8 rows — E03 Nonaktif)');

        // ── 3.6  Employee Private Records (HR-restricted, SR-15) ──────────
        DB::table('employee_private_records')->insert([
            ['employee_id' => 'E01', 'personal_email' => 'sari.private@example.test', 'emergency_contact' => 'Nila Prameswari', 'private_phone' => '0812-0000-0101', 'created_at' => $now, 'updated_at' => $now],
            ['employee_id' => 'E03', 'personal_email' => 'naya.private@example.test', 'emergency_contact' => 'Laras Putri',     'private_phone' => '0812-0000-0303', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->line('   seeded   employee_private_records (2 rows)');

        // ── 3.7  Contacts — Customers ─────────────────────────────────────
        // Handout §4, Customer Master. C01 & C02 carry open AR.
        DB::table('contacts_customers')->insert([
            ['id' => 'C01', 'name' => 'PT Toko Makmur Sejahtera', 'type' => 'B2B', 'city' => 'Jakarta',   'credit_limit' => 2_000_000, 'payment_terms' => '14 hari', 'open_ar' => 900_000, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'C02', 'name' => 'CV Sinar Harapan',         'type' => 'B2B', 'city' => 'Bekasi',    'credit_limit' => 1_000_000, 'payment_terms' => '14 hari', 'open_ar' => 600_000, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'C03', 'name' => 'UD Berkah Niaga',          'type' => 'B2B', 'city' => 'Depok',     'credit_limit' => 3_000_000, 'payment_terms' => '30 hari', 'open_ar' => 0,       'created_at' => $now, 'updated_at' => $now],
            ['id' => 'C04', 'name' => 'Toko Aneka Jaya',          'type' => 'B2B', 'city' => 'Bogor',     'credit_limit' => 1_250_000, 'payment_terms' => 'Tunai',   'open_ar' => 0,       'created_at' => $now, 'updated_at' => $now],
            ['id' => 'C05', 'name' => 'PT Mini Mart Bersama',     'type' => 'B2B', 'city' => 'Tangerang', 'credit_limit' => 4_000_000, 'payment_terms' => '30 hari', 'open_ar' => 0,       'created_at' => $now, 'updated_at' => $now],
            ['id' => 'C06', 'name' => 'Toko Karunia',             'type' => 'B2B', 'city' => 'Jakarta',   'credit_limit' => 750_000,   'payment_terms' => 'Tunai',   'open_ar' => 0,       'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->line('   seeded   contacts_customers (6 rows — C01 AR 900k, C02 AR 600k)');

        // ── 3.8  Contacts — Vendors ───────────────────────────────────────
        DB::table('contacts_vendors')->insert([
            ['id' => 'V01', 'name' => 'PT Bersih Abadi',    'city' => 'Jakarta',   'payment_terms' => '14 hari', 'category' => 'Produk kebersihan',  'created_at' => $now, 'updated_at' => $now],
            ['id' => 'V02', 'name' => 'CV Pangan Rasa',     'city' => 'Bekasi',    'payment_terms' => '14 hari', 'category' => 'Makanan ringan',     'created_at' => $now, 'updated_at' => $now],
            ['id' => 'V03', 'name' => 'PT Segar Nusantara', 'city' => 'Tangerang', 'payment_terms' => '30 hari', 'category' => 'Minuman',            'created_at' => $now, 'updated_at' => $now],
            ['id' => 'V04', 'name' => 'PT Personal Sentosa','city' => 'Bogor',     'payment_terms' => '14 hari', 'category' => 'Perawatan pribadi',  'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->line('   seeded   contacts_vendors (4 rows — V04 has open AP Rp700k)');

        // ── 3.9  Products (P01 – P16) ─────────────────────────────────────
        DB::table('products')->insert([
            ['id' => 'P01', 'variant_sku' => 'CLN-DTR-800-LMN', 'name' => 'Detergen Cair Lemon',      'size' => '800 ml',    'color' => 'Lemon',    'unit' => 'pouch',  'sales_price' => 25_000, 'cost' => 17_000, 'default_vendor' => 'V01', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P02', 'variant_sku' => 'FOD-MIE-B12-ORI', 'name' => 'Mie Instan Dus Original',  'size' => '12 pcs',    'color' => 'Original', 'unit' => 'dus',    'sales_price' => 54_000, 'cost' => 37_000, 'default_vendor' => 'V02', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P03', 'variant_sku' => 'BEV-TEA-C24-LCH', 'name' => 'Teh Botol Karton Leci',   'size' => '24 botol',  'color' => 'Leci',     'unit' => 'karton', 'sales_price' => 72_000, 'cost' => 51_000, 'default_vendor' => 'V03', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P04', 'variant_sku' => 'CLN-DIS-750-JER', 'name' => 'Sabun Cuci Piring Jeruk', 'size' => '750 ml',    'color' => 'Jeruk',    'unit' => 'botol',  'sales_price' => 20_000, 'cost' => 13_000, 'default_vendor' => 'V01', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P05', 'variant_sku' => 'PRS-SHM-B24-MNT', 'name' => 'Sampo Sachet Dus Mentol', 'size' => '24 sachet', 'color' => 'Mentol',   'unit' => 'dus',    'sales_price' => 48_000, 'cost' => 32_000, 'default_vendor' => 'V04', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P06', 'variant_sku' => 'PRS-SPR-100-ROS', 'name' => 'Sabun Mandi Mawar',       'size' => '100 ml',    'color' => 'Mawar',    'unit' => 'botol',  'sales_price' => 14_000, 'cost' =>  9_000, 'default_vendor' => 'V04', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P07', 'variant_sku' => 'SNK-BIS-B10-CHO', 'name' => 'Biskuit Cokelat Dus',     'size' => '10 pak',    'color' => 'Cokelat',  'unit' => 'dus',    'sales_price' => 62_000, 'cost' => 44_000, 'default_vendor' => 'V02', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P08', 'variant_sku' => 'SNK-WFR-B12-VNL', 'name' => 'Wafer Vanila Dus',        'size' => '12 pak',    'color' => 'Vanila',   'unit' => 'dus',    'sales_price' => 52_000, 'cost' => 36_000, 'default_vendor' => 'V02', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P09', 'variant_sku' => 'BEV-COF-B20-ORI', 'name' => 'Kopi Sachet Dus',         'size' => '20 sachet', 'color' => 'Original', 'unit' => 'dus',    'sales_price' => 68_000, 'cost' => 48_000, 'default_vendor' => 'V03', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P10', 'variant_sku' => 'CLN-FLR-900-PIN', 'name' => 'Pembersih Lantai Pine',   'size' => '900 ml',    'color' => 'Pine',     'unit' => 'botol',  'sales_price' => 28_000, 'cost' => 18_000, 'default_vendor' => 'V01', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P11', 'variant_sku' => 'CLN-DTR-800-FLR', 'name' => 'Detergen Cair Floral',    'size' => '800 ml',    'color' => 'Floral',   'unit' => 'pouch',  'sales_price' => 25_000, 'cost' => 17_000, 'default_vendor' => 'V01', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P12', 'variant_sku' => 'BEV-TEA-C24-JSM', 'name' => 'Teh Botol Karton Melati', 'size' => '24 botol',  'color' => 'Melati',   'unit' => 'karton', 'sales_price' => 72_000, 'cost' => 51_000, 'default_vendor' => 'V03', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P13', 'variant_sku' => 'FOD-MIE-B12-SPC', 'name' => 'Mie Instan Dus Pedas',    'size' => '12 pcs',    'color' => 'Pedas',    'unit' => 'dus',    'sales_price' => 54_000, 'cost' => 37_000, 'default_vendor' => 'V02', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P14', 'variant_sku' => 'PRS-TOO-120-HRB', 'name' => 'Pasta Gigi Herbal',       'size' => '120 g',     'color' => 'Herbal',   'unit' => 'tube',   'sales_price' => 19_000, 'cost' => 12_000, 'default_vendor' => 'V04', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P15', 'variant_sku' => 'SNK-CRK-B08-BBQ', 'name' => 'Kerupuk BBQ Dus',         'size' => '8 pak',     'color' => 'BBQ',      'unit' => 'dus',    'sales_price' => 47_000, 'cost' => 30_000, 'default_vendor' => 'V02', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 'P16', 'variant_sku' => 'PRS-SHM-B24-ALV', 'name' => 'Sampo Sachet Dus Aloe',   'size' => '24 sachet', 'color' => 'Aloe',     'unit' => 'dus',    'sales_price' => 48_000, 'cost' => 32_000, 'default_vendor' => 'V04', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->line('   seeded   products (16 rows P01–P16)');

        // ── 3.10  Vendor Pricelist ─────────────────────────────────────────
        // One default-vendor entry per SKU matching products.cost
        DB::table('vendor_pricelist')->insert([
            ['vendor_id' => 'V01', 'variant_sku' => 'CLN-DTR-800-LMN', 'unit' => 'pouch',  'unit_price' => 17_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V02', 'variant_sku' => 'FOD-MIE-B12-ORI', 'unit' => 'dus',    'unit_price' => 37_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V03', 'variant_sku' => 'BEV-TEA-C24-LCH', 'unit' => 'karton', 'unit_price' => 51_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V01', 'variant_sku' => 'CLN-DIS-750-JER', 'unit' => 'botol',  'unit_price' => 13_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V04', 'variant_sku' => 'PRS-SHM-B24-MNT', 'unit' => 'dus',    'unit_price' => 32_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V04', 'variant_sku' => 'PRS-SPR-100-ROS', 'unit' => 'botol',  'unit_price' =>  9_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V02', 'variant_sku' => 'SNK-BIS-B10-CHO', 'unit' => 'dus',    'unit_price' => 44_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V02', 'variant_sku' => 'SNK-WFR-B12-VNL', 'unit' => 'dus',    'unit_price' => 36_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V03', 'variant_sku' => 'BEV-COF-B20-ORI', 'unit' => 'dus',    'unit_price' => 48_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V01', 'variant_sku' => 'CLN-FLR-900-PIN', 'unit' => 'botol',  'unit_price' => 18_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V01', 'variant_sku' => 'CLN-DTR-800-FLR', 'unit' => 'pouch',  'unit_price' => 17_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V03', 'variant_sku' => 'BEV-TEA-C24-JSM', 'unit' => 'karton', 'unit_price' => 51_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V02', 'variant_sku' => 'FOD-MIE-B12-SPC', 'unit' => 'dus',    'unit_price' => 37_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V04', 'variant_sku' => 'PRS-TOO-120-HRB', 'unit' => 'tube',   'unit_price' => 12_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V02', 'variant_sku' => 'SNK-CRK-B08-BBQ', 'unit' => 'dus',    'unit_price' => 30_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['vendor_id' => 'V04', 'variant_sku' => 'PRS-SHM-B24-ALV', 'unit' => 'dus',    'unit_price' => 32_000, 'min_qty' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->line('   seeded   vendor_pricelist (16 rows)');

        // ── 3.11  Warehouse Locations ──────────────────────────────────────
        DB::table('warehouses_locations')->insert([
            ['code' => 'WH-JKT',  'name' => 'Gudang Utama Arunika',    'city' => 'Jakarta', 'usage' => 'Internal', 'created_at' => $now, 'updated_at' => $now],
            ['code' => 'HUB-BDG', 'name' => 'Hub Distribusi Bandung',  'city' => 'Bandung', 'usage' => 'Internal', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->line('   seeded   warehouses_locations (2 rows)');

        // ── 3.12  Opening Stock ────────────────────────────────────────────
        //
        //  Verification:
        //   Company total on-hand = SUM(company_total) = 880 units          ✓
        //   Standard value        = SUM(standard_value) = Rp23,095,000      ✓
        //   WH-JKT subtotal       = Rp17,315,000
        //   HUB-BDG subtotal      = Rp 5,780,000
        //
        //  P01 (CLN-DTR-800-LMN): wh_jkt=60 is gross on-hand; 8 units are
        //  reserved for S00007 (reflected in reserved=8 → available=52).
        //  The lot table for P03 sums to 50 = company_total; no double-count.
        //
        DB::table('opening_stock')->insert([
            // SKU                    WH-JKT  HUB-BDG  total  reserved  cost    value
            ['variant_sku' => 'CLN-DTR-800-LMN', 'wh_jkt_on_hand' => 60, 'hub_bdg_on_hand' => 20, 'company_total' => 80,  'reserved' => 8, 'standard_cost' => 17_000, 'standard_value' => 1_360_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'FOD-MIE-B12-ORI', 'wh_jkt_on_hand' => 40, 'hub_bdg_on_hand' => 10, 'company_total' => 50,  'reserved' => 0, 'standard_cost' => 37_000, 'standard_value' => 1_850_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'BEV-TEA-C24-LCH', 'wh_jkt_on_hand' => 35, 'hub_bdg_on_hand' => 15, 'company_total' => 50,  'reserved' => 0, 'standard_cost' => 51_000, 'standard_value' => 2_550_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'CLN-DIS-750-JER', 'wh_jkt_on_hand' => 50, 'hub_bdg_on_hand' => 20, 'company_total' => 70,  'reserved' => 0, 'standard_cost' => 13_000, 'standard_value' =>   910_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'PRS-SHM-B24-MNT', 'wh_jkt_on_hand' => 30, 'hub_bdg_on_hand' => 10, 'company_total' => 40,  'reserved' => 0, 'standard_cost' => 32_000, 'standard_value' => 1_280_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'PRS-SPR-100-ROS', 'wh_jkt_on_hand' => 80, 'hub_bdg_on_hand' => 20, 'company_total' => 100, 'reserved' => 0, 'standard_cost' =>  9_000, 'standard_value' =>   900_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'SNK-BIS-B10-CHO', 'wh_jkt_on_hand' => 25, 'hub_bdg_on_hand' => 10, 'company_total' => 35,  'reserved' => 0, 'standard_cost' => 44_000, 'standard_value' => 1_540_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'SNK-WFR-B12-VNL', 'wh_jkt_on_hand' => 30, 'hub_bdg_on_hand' => 10, 'company_total' => 40,  'reserved' => 0, 'standard_cost' => 36_000, 'standard_value' => 1_440_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'BEV-COF-B20-ORI', 'wh_jkt_on_hand' => 20, 'hub_bdg_on_hand' => 10, 'company_total' => 30,  'reserved' => 0, 'standard_cost' => 48_000, 'standard_value' => 1_440_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'CLN-FLR-900-PIN', 'wh_jkt_on_hand' => 45, 'hub_bdg_on_hand' => 15, 'company_total' => 60,  'reserved' => 0, 'standard_cost' => 18_000, 'standard_value' => 1_080_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'CLN-DTR-800-FLR', 'wh_jkt_on_hand' => 55, 'hub_bdg_on_hand' => 15, 'company_total' => 70,  'reserved' => 0, 'standard_cost' => 17_000, 'standard_value' => 1_190_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'BEV-TEA-C24-JSM', 'wh_jkt_on_hand' => 40, 'hub_bdg_on_hand' => 10, 'company_total' => 50,  'reserved' => 0, 'standard_cost' => 51_000, 'standard_value' => 2_550_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'FOD-MIE-B12-SPC', 'wh_jkt_on_hand' => 35, 'hub_bdg_on_hand' => 10, 'company_total' => 45,  'reserved' => 0, 'standard_cost' => 37_000, 'standard_value' => 1_665_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'PRS-TOO-120-HRB', 'wh_jkt_on_hand' => 65, 'hub_bdg_on_hand' => 20, 'company_total' => 85,  'reserved' => 0, 'standard_cost' => 12_000, 'standard_value' => 1_020_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'SNK-CRK-B08-BBQ', 'wh_jkt_on_hand' => 30, 'hub_bdg_on_hand' => 10, 'company_total' => 40,  'reserved' => 0, 'standard_cost' => 30_000, 'standard_value' => 1_200_000, 'created_at' => $now, 'updated_at' => $now],
            ['variant_sku' => 'PRS-SHM-B24-ALV', 'wh_jkt_on_hand' => 25, 'hub_bdg_on_hand' => 10, 'company_total' => 35,  'reserved' => 0, 'standard_cost' => 32_000, 'standard_value' => 1_120_000, 'created_at' => $now, 'updated_at' => $now],
            // ─────────────────────────────────────────────────────────────
            // TOTALS (verified):
            //   company_total = 80+50+50+70+40+100+35+40+30+60+70+50+45+85+40+35 = 880 ✓
            //   standard_value= 1,360,000+1,850,000+2,550,000+910,000+1,280,000
            //                  +900,000+1,540,000+1,440,000+1,440,000+1,080,000
            //                  +1,190,000+2,550,000+1,665,000+1,020,000
            //                  +1,200,000+1,120,000 = 23,095,000                 ✓
        ]);
        $this->line('   seeded   opening_stock (16 rows — 880 units, Rp23.095.000)');

        // ── 3.13  Product Lots — P03 BEV-TEA-C24-LCH (FEFO) ──────────────
        //
        //  LOT-TEA-01  15 qty  WH-JKT   exp 2026-11-15   ← earliest, picked first
        //  LOT-TEA-02  20 qty  WH-JKT   exp 2027-02-28
        //  LOT-TEA-03  15 qty  HUB-BDG  exp 2027-03-15
        //  ──────────────────────────────────────────────
        //  Total       50 qty  == opening_stock company_total for BEV-TEA-C24-LCH ✓
        //  No double-counting: lots decompose the 50 units by location & expiry.
        //
        DB::table('product_lots')->insert([
            ['lot_id' => 'LOT-TEA-01', 'variant_sku' => 'BEV-TEA-C24-LCH', 'location' => 'WH-JKT',  'qty' => 15, 'expiry_date' => '2026-11-15', 'selection_policy' => 'FEFO', 'created_at' => $now, 'updated_at' => $now],
            ['lot_id' => 'LOT-TEA-02', 'variant_sku' => 'BEV-TEA-C24-LCH', 'location' => 'WH-JKT',  'qty' => 20, 'expiry_date' => '2027-02-28', 'selection_policy' => 'FEFO', 'created_at' => $now, 'updated_at' => $now],
            ['lot_id' => 'LOT-TEA-03', 'variant_sku' => 'BEV-TEA-C24-LCH', 'location' => 'HUB-BDG', 'qty' => 15, 'expiry_date' => '2027-03-15', 'selection_policy' => 'FEFO', 'created_at' => $now, 'updated_at' => $now],
        ]);
        $this->line('   seeded   product_lots (3 rows — P03 FEFO total 50 = 15+20+15)');

        // ── 3.14  Audit Logs ──────────────────────────────────────────────
        $logs = [];

        // Employee creation (system seeder)
        foreach (['E01','E02','E03','E04','E05','E06','E07','E08'] as $eid) {
            $logs[] = [
                'document_type'  => 'employee',
                'document_id'    => $eid,
                'action'         => 'created',
                'actor_user'     => 'system',
                'actor_employee' => null,
                'description'    => "Employee {$eid} dibuat via master seed.",
                'old_values'     => null,
                'new_values'     => null,
                'acted_at'       => $now,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        // S00007 — Sales Order confirm & stock reservation (T-35, SR-12, SR-22)
        $logs[] = [
            'document_type'  => 'sales_order',
            'document_id'    => 'S00007',
            'action'         => 'confirm',
            'actor_user'     => 'sales_user',
            'actor_employee' => 'E04',
            'description'    => 'SO S00007 dikonfirmasi. Customer: C01, 8×P01 CLN-DTR-800-LMN, net Rp200.000, gross Rp222.000. Harga terkunci (SR-12).',
            'old_values'     => '{"status":"draft"}',
            'new_values'     => '{"status":"confirmed"}',
            'acted_at'       => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];
        $logs[] = [
            'document_type'  => 'sales_order',
            'document_id'    => 'S00007',
            'action'         => 'reserve',
            'actor_user'     => 'warehouse_mgr',
            'actor_employee' => 'E01',
            'description'    => 'Reservasi 8 unit P01 di WH-JKT untuk S00007. On Hand 60, Reserved 8, Available 52. (SR-22)',
            'old_values'     => '{"reserved":0,"available":60}',
            'new_values'     => '{"reserved":8,"available":52}',
            'acted_at'       => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        // PO00007 — Purchase Order confirm (T-35, T-36, SR-21)
        $logs[] = [
            'document_type'  => 'purchase_order',
            'document_id'    => 'PO00007',
            'action'         => 'confirm',
            'actor_user'     => 'purchase_user',
            'actor_employee' => 'E07',
            'description'    => 'PO00007 dikonfirmasi. Vendor: V04 PT Personal Sentosa, 20×P05 PRS-SHM-B24-MNT, net Rp640.000, gross est. Rp710.400. Unit price terkunci (SR-21).',
            'old_values'     => '{"status":"rfq"}',
            'new_values'     => '{"status":"confirmed"}',
            'acted_at'       => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        // L00003 — CRM Lead created (F-21, T-07, T-08)
        $logs[] = [
            'document_type'  => 'lead',
            'document_id'    => 'L00003',
            'action'         => 'created',
            'actor_user'     => 'sales_user',
            'actor_employee' => 'E04',
            'description'    => 'Lead L00003 dibuat. Customer: C03 UD Berkah Niaga. Expected Revenue: Rp500.000. Salesperson: E04.',
            'old_values'     => null,
            'new_values'     => '{"stage":"new","customer":"C03","revenue":500000}',
            'acted_at'       => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        // Login events (F-01, SR-15, T-01, T-02)
        foreach (['sales_user' => 'Sales', 'warehouse_mgr' => 'Warehouse', 'hr_user' => 'HR', 'owner_user' => 'Owner'] as $uname => $role) {
            $logs[] = [
                'document_type'  => 'access_control',
                'document_id'    => $uname,
                'action'         => 'login',
                'actor_user'     => $uname,
                'actor_employee' => null,
                'description'    => "User [{$uname}] login berhasil. Role: {$role}. (F-01, SR-15)",
                'old_values'     => null,
                'new_values'     => null,
                'acted_at'       => $now,
                'created_at'     => $now,
                'updated_at'     => $now,
            ];
        }

        // E03 status changed → Nonaktif (SR-18, T-30)
        $logs[] = [
            'document_type'  => 'employee',
            'document_id'    => 'E03',
            'action'         => 'status_changed',
            'actor_user'     => 'hr_user',
            'actor_employee' => 'E08',
            'description'    => 'Status E03 Naya Putri diubah Aktif→Nonaktif. Efektif segera (SR-18). E03 tidak boleh sign-off dokumen gudang.',
            'old_values'     => '{"status":"Aktif"}',
            'new_values'     => '{"status":"Nonaktif"}',
            'acted_at'       => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        // Access denied — sales_user → accounting page (SR-15, T-02)
        $logs[] = [
            'document_type'  => 'access_control',
            'document_id'    => 'sales_user',
            'action'         => 'access_denied',
            'actor_user'     => 'sales_user',
            'actor_employee' => 'E04',
            'description'    => 'Role [Sales] ditolak akses ke [accounting]. Hanya Finance/Owner. (SR-15, T-02)',
            'old_values'     => null,
            'new_values'     => null,
            'acted_at'       => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        // E03 sign-off rejected (SR-17, SR-18, T-18)
        $logs[] = [
            'document_type'  => 'receipt',
            'document_id'    => 'RCV-ATTEMPT-001',
            'action'         => 'sign_off_rejected',
            'actor_user'     => 'system',
            'actor_employee' => 'E03',
            'description'    => 'Sign-off ditolak: E03 Naya Putri berstatus Nonaktif (SR-17, SR-18). Hanya Employee Aktif Warehouse dengan mapped_user boleh sign-off. (T-18)',
            'old_values'     => null,
            'new_values'     => null,
            'acted_at'       => $now,
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        DB::table('audit_logs')->insert($logs);
        $this->line('   seeded   audit_logs (' . count($logs) . ' rows)');

        // ── 3.15  Leads (CRM seed — F-04, T-07, T-08) ────────────────────
        DB::table('leads')->insert([
            [
                'id'               => 'L00001',
                'partner_id'       => 'C01',
                'partner_name'     => 'PT Toko Makmur Sejahtera',
                'stage'            => 'won',
                'salesperson_id'   => 'sales_user',
                'salesperson_name' => 'Dini Pratiwi',
                'expected_revenue' => 1_000_000,
                'notes'            => 'Sudah jadi pelanggan tetap.',
                'created_by'       => 'sales_user',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'id'               => 'L00002',
                'partner_id'       => 'C02',
                'partner_name'     => 'CV Sinar Harapan',
                'stage'            => 'qualified',
                'salesperson_id'   => 'sales_user2',
                'salesperson_name' => 'Farhan Saputra',
                'expected_revenue' => 750_000,
                'notes'            => null,
                'created_by'       => 'sales_user2',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                // Handout §4 existing document: L00003, C03, New, Rp500.000
                'id'               => 'L00003',
                'partner_id'       => 'C03',
                'partner_name'     => 'UD Berkah Niaga',
                'stage'            => 'new',
                'salesperson_id'   => 'sales_user',
                'salesperson_name' => 'Dini Pratiwi',
                'expected_revenue' => 500_000,
                'notes'            => 'Prospek baru Depok.',
                'created_by'       => 'sales_user',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);
        $this->line('   seeded   leads (3 rows — L00001, L00002, L00003)');

        // ── 3.16  Sales Orders & Lines (seed — F-05, F-10, T-03, T-09) ───
        // S00007: Handout §4 existing document — 8×P01 Confirmed, reserved, unbilled
        DB::table('sales_orders')->insert([
            [
                'id'                       => 'S00007',
                'customer_id'              => 'C01',
                'customer_name'            => 'PT Toko Makmur Sejahtera',
                'status'                   => 'confirmed',
                'discount_approval_status' => 'not_required',
                'discount_approved_by'     => null,
                'discount_approved_at'     => null,
                'untaxed_amount'           => 200_000,   // 8 × 25.000
                'tax_amount'               => 22_000,    // floor(200000×0.11+0.5)
                'gross_amount'             => 222_000,   // 200.000 + 22.000
                'payment_terms'            => '14 hari',
                'order_date'               => '2026-10-20',
                'delivery_promise_date'    => '2026-10-23',
                'created_by'               => 'sales_user',
                'confirmed_by'             => 'sales_user',
                'confirmed_at'             => $now,
                'lead_id'                  => 'L00001',
                'created_at'               => $now,
                'updated_at'               => $now,
            ],
        ]);

        DB::table('sales_order_lines')->insert([
            [
                'sales_order_id'   => 'S00007',
                'product_id'       => 'P01',
                'variant_sku'      => 'CLN-DTR-800-LMN',
                'product_name'     => 'Detergen Cair Lemon',
                'unit'             => 'pouch',
                'quantity'         => 8,
                'unit_price'       => 25_000,
                'discount_percent' => 0.00,
                'is_locked'        => true,            // confirmed → locked (SR-12)
                'net_amount'       => 200_000,
                'tax_amount'       => 22_000,
                'gross_amount'     => 222_000,
                'delivered_qty'    => 0,               // not yet delivered
                'invoiced_qty'     => 0,
                'reserved_qty'     => 8,               // SR-22: 8 units reserved at WH-JKT
                'source_location'  => 'WH-JKT',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ]);
        $this->line('   seeded   sales_orders (S00007 — Confirmed, 8×P01, gross Rp222.000)');
        $this->line('   seeded   sales_order_lines (1 row — S00007 line)');

        // ── 3.17  Purchase Order PO00007 (existing document from handout §4) ──
        DB::table('purchase_orders')->insert([[
            'id'           => 'PO00007',
            'vendor_id'    => 'V04',
            'vendor_name'  => 'PT Personal Sentosa',
            'status'       => 'confirmed',
            'is_locked'    => 1,
            'net_amount'   => 640_000,
            'tax_amount'   => 70_400,
            'gross_amount' => 710_400,
            'payment_terms'=> '14 hari',
            'order_date'   => '2026-10-20',
            'expected_arrival' => null,
            'created_by'   => 'purchase_user',
            'confirmed_by' => 'purchase_user',
            'confirmed_at' => '2026-10-20 09:00:00',
            'created_at'   => $now, 'updated_at' => $now,
        ]]);
        DB::table('purchase_order_lines')->insert([[
            'purchase_order_id' => 'PO00007',
            'product_id'        => 'P05',
            'variant_sku'       => 'PRS-SHM-B24-MNT',
            'product_name'      => 'Sampo Sachet Dus Mentol',
            'unit'              => 'dus',
            'quantity'          => 20,
            'unit_price'        => 32_000,
            'is_locked'         => 1,
            'net_amount'        => 640_000,
            'tax_amount'        => 70_400,
            'gross_amount'      => 710_400,
            'received_qty'      => 0,
            'accepted_qty'      => 0,
            'rejected_qty'      => 0,
            'billed_qty'        => 0,
            'created_at'        => $now, 'updated_at' => $now,
        ]]);
        $this->line('   seeded   purchase_orders (1 row — PO00007 Confirmed V04 20×P05)');
        $this->line('   seeded   purchase_order_lines (1 row)');
    }

    // ────────────────────────────────────────────────────────────────────────
    //  Helpers
    // ────────────────────────────────────────────────────────────────────────
    private function step(string $step, string $msg): void
    {
        $this->info('');
        $this->info("  [{$step}] {$msg}");
    }
}
