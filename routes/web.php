<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AccountingController;
use App\Http\Controllers\Sales\LeadController;
use App\Http\Controllers\Sales\SalesOrderController;

// ── Auth ─────────────────────────────────────────────────────────────────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', fn() => redirect()->route('home'));

Route::middleware(['auth'])->group(function () {

    // Home / App Switcher (S-01, F-01, T-01, T-02)
    Route::get('/home', fn() => view('home'))->name('home');

    // Wireframes S-01–S-18 (M3-W1-01)
    Route::get('/wireframes', fn() => view('wireframes.index'))->name('wireframes');

    // ── Employees (M3) ────────────────────────────────────────────────
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::post('/employees/{id}/toggle-status', [EmployeeController::class, 'toggleStatus'])
         ->name('employees.toggleStatus')->middleware('role:HR,Owner');
    Route::post('/employees/validate-signoff', [EmployeeController::class, 'validateSignOff'])
         ->name('employees.validateSignOff');

    // ── Reporting / Audit (M3-W3-04 · F-21 · S-18) ───────────────────────
    Route::get('/reporting/audit', [AuditController::class, 'index'])
         ->name('reporting.audit');
    Route::get('/reporting/audit/{docType}/{docId}', [AuditController::class, 'show'])
         ->name('reporting.audit.show')
         ->where('docId', '.*');

    // ── CRM / Leads (M1 · F-04 · S-04 · T-07, T-08) ─────────────────────
    Route::middleware('role:Sales,Owner')->prefix('crm')->name('crm.')->group(function () {
        Route::get('/leads',                               [LeadController::class, 'index'])->name('leads.index');
        Route::post('/leads',                              [LeadController::class, 'store'])->name('leads.store');
        Route::get('/leads/{id}',                          [LeadController::class, 'show'])->name('leads.show');
        Route::post('/leads/{id}/convert',                 [LeadController::class, 'convertToOpportunity'])->name('leads.convert');
        Route::post('/leads/{id}/update-customer',         [LeadController::class, 'updateCustomer'])->name('leads.update-customer');
    });

    // ── Sales Orders (M1 · F-05, F-06, F-10, F-22 · S-05 · T-03,T-04,T-09,T-10) ──
    Route::prefix('sales')->name('sales.')->group(function () {
        // Read access: Sales + Owner
        Route::middleware('role:Sales,Owner')->group(function () {
            Route::get('/orders',                          [SalesOrderController::class, 'index'])->name('orders.index');
            Route::get('/orders/create',                   [SalesOrderController::class, 'create'])->name('orders.create');
            Route::post('/orders',                         [SalesOrderController::class, 'store'])->name('orders.store');
            Route::get('/orders/{id}',                     [SalesOrderController::class, 'show'])->name('orders.show');
            Route::post('/orders/{id}/confirm',            [SalesOrderController::class, 'confirm'])->name('orders.confirm');
            Route::post('/orders/{id}/cancel',             [SalesOrderController::class, 'cancel'])->name('orders.cancel');
            // API: credit exposure check (used by create-form JS)
            Route::get('/api/credit/{customerId}',         [SalesOrderController::class, 'creditExposure'])->name('api.credit');
        });

        // Discount approval: Finance + Owner only (SR-08)
        Route::middleware('role:Finance,Owner')->group(function () {
            Route::post('/orders/{id}/approve-discount',   [SalesOrderController::class, 'approveDiscount'])->name('orders.approve-discount');
            Route::post('/orders/{id}/reject-discount',    [SalesOrderController::class, 'rejectDiscount'])->name('orders.reject-discount');
        });
    });

    // ── Accounting & Invoicing (M4) ───────────────────────────────────────
    Route::middleware('role:Finance,Owner')->group(function () {
        // Customer Invoices (F-09, S-10)
        Route::get('/accounting/invoices',                                    [AccountingController::class, 'invoiceIndex'])->name('accounting.invoices.index');
        Route::get('/accounting/invoices/create',                             [AccountingController::class, 'invoiceCreate'])->name('accounting.invoices.create');
        Route::post('/accounting/invoices',                                   [AccountingController::class, 'invoiceStore'])->name('accounting.invoices.store');
        Route::get('/accounting/invoices/{id}',                               [AccountingController::class, 'invoiceShow'])->name('accounting.invoices.show');
        Route::post('/accounting/invoices/{id}/post',                         [AccountingController::class, 'invoicePost'])->name('accounting.invoices.post');
        Route::get('/accounting/invoices/{id}/credit-note/create',            [AccountingController::class, 'creditNoteCreate'])->name('accounting.credit-notes.create');
        Route::post('/accounting/invoices/{id}/credit-note',                  [AccountingController::class, 'creditNoteStore'])->name('accounting.credit-notes.store');

        // Vendor Bills (F-13, S-11)
        Route::get('/accounting/bills',                                       [AccountingController::class, 'billIndex'])->name('accounting.bills.index');
        Route::post('/accounting/bills',                                      [AccountingController::class, 'billStore'])->name('accounting.bills.store');
        Route::get('/accounting/bills/{id}',                                  [AccountingController::class, 'billShow'])->name('accounting.bills.show');
        Route::post('/accounting/bills/{id}/post',                            [AccountingController::class, 'billPost'])->name('accounting.bills.post');

        // Register Payment (F-15, S-12)
        Route::get('/accounting/payments/{invoiceId}/create',                 [AccountingController::class, 'paymentCreate'])->name('accounting.payments.create');
        Route::post('/accounting/payments/{invoiceId}',                       [AccountingController::class, 'paymentStore'])->name('accounting.payments.store');

        // General Ledger (F-14, S-13)
        Route::get('/accounting/ledger',                                      [AccountingController::class, 'ledgerIndex'])->name('accounting.ledger.index');

        // Chart of Accounts overview
        Route::get('/accounting/coa',                                         [AccountingController::class, 'coaIndex'])->name('accounting.coa.index');
    });
});
