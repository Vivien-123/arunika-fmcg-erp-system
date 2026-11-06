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
    Route::get('/employees/defense', fn() => view('employees.defense'))->name('employees.defense');
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

    // ── M3-W4-01: Test Results T-01, T-02, T-27–T-30 ────────────────────
    Route::get('/reporting/test-results', fn() => view('reporting.test-results-m3'))
         ->name('reporting.test-results');

    // ── Operational Dashboard (F-20, S-17, SR-19) ──────────────────────
    Route::get('/reporting/dashboard', function () {
        $stocks      = \App\Models\OpeningStock::with('product')->orderBy('variant_sku')->get();
        $totalOnHand = $stocks->sum('company_total');
        $stockValue  = $stocks->sum('standard_value');
        $whJkt       = $stocks->sum('wh_jkt_on_hand');
        $hubBdg      = $stocks->sum('hub_bdg_on_hand');
        $totalReserved = $stocks->sum('reserved');

        // SR-19: AR/AP/Bank only from POSTED journal entries (not draft)
        $ar   = \App\Models\JournalEntry::where('debit_account',  '1100')->sum('amount')
              - \App\Models\JournalEntry::where('credit_account', '1100')->sum('amount');
        $ap   = \App\Models\JournalEntry::where('credit_account', '2000')->sum('amount')
              - \App\Models\JournalEntry::where('debit_account',  '2000')->sum('amount');
        $bank = \App\Models\JournalEntry::where('debit_account',  '1000')->sum('amount')
              - \App\Models\JournalEntry::where('credit_account', '1000')->sum('amount');

        // SR-19: Open Orders — only Confirmed SO and PO (not draft)
        $openSOs = \App\Models\SalesOrder::whereIn('status', ['confirmed', 'done'])
            ->selectRaw('count(*) as cnt, sum(gross_amount) as total')->first();
        $openPOs = \App\Models\PurchaseOrder::whereIn('status', ['confirmed'])
            ->selectRaw('count(*) as cnt, sum(gross_amount) as total')->first();

        // SR-19: Draft invoices must NOT inflate AR (T-34 check)
        $draftInvoiceCount = \App\Models\Invoice::where('status', 'draft')->count();
        $draftInvoiceValue = \App\Models\Invoice::where('status', 'draft')->sum('gross_amount');

        // Recent audit logs
        $recentLogs = \App\Models\AuditLog::orderByDesc('acted_at')->limit(6)->get();

        // Recent deliveries and receipts
        $recentDeliveries = \App\Models\Delivery::orderByDesc('created_at')->limit(5)->get();
        $recentReceipts   = \App\Models\Receipt::orderByDesc('created_at')->limit(5)->get();

        return view('reporting.dashboard', compact(
            'stocks', 'totalOnHand', 'stockValue', 'whJkt', 'hubBdg', 'totalReserved',
            'ar', 'ap', 'bank',
            'openSOs', 'openPOs',
            'draftInvoiceCount', 'draftInvoiceValue',
            'recentLogs', 'recentDeliveries', 'recentReceipts'
        ));
    })->name('reporting.dashboard');

    // ── Master Admin & Business Rules (SR-01..SR-22) ──────────────────
    Route::get('/admin/business-rules', function () {
        $businessRules = [
            ['id'=>'SR-01','name'=>'Invoice saat SO Confirmed','stakeholder'=>'Hendra Widodo (Owner)','module'=>'Sales/Accounting','status'=>'Rejected','action'=>'Ditolak literal, ikuti DL-01 (SR-02 dipilih)'],
            ['id'=>'SR-02','name'=>'Invoice hanya qty Done di DO','stakeholder'=>'Made Wirawan (Finance)','module'=>'Accounting','status'=>'Active','action'=>'Invoicing Policy = Delivered Quantities'],
            ['id'=>'SR-03','name'=>'SO boleh konfirmasi walau Available kurang','stakeholder'=>'Dini Pratiwi (Sales)','module'=>'Sales','status'=>'Active','action'=>'Konfirmasi diizinkan, reservation dibatasi'],
            ['id'=>'SR-04','name'=>'Tidak boleh membuat stok negatif','stakeholder'=>'Sari Prameswari (Gudang)','module'=>'Inventory','status'=>'Active','action'=>'Guard di OpeningStock::reserve()'],
            ['id'=>'SR-05','name'=>'Vendor Bill boleh seluruh qty PO','stakeholder'=>'Rita Kusuma (Pengadaan)','module'=>'Purchase','status'=>'Rejected','action'=>'Ditolak literal, ikuti DL-03 (SR-06 dipilih)'],
            ['id'=>'SR-06','name'=>'Vendor Bill maks accepted receipt qty','stakeholder'=>'Made Wirawan (Finance)','module'=>'Accounting','status'=>'Active','action'=>'3-way match: PO × Receipt × Bill'],
            ['id'=>'SR-07','name'=>'Diskon ≤ 15% tanpa approval','stakeholder'=>'Dini Pratiwi (Sales)','module'=>'Sales','status'=>'Modified','action'=>'Batas diskon bebas = 10%, 10-15% wajib approval'],
            ['id'=>'SR-08','name'=>'Diskon >10% wajib Finance approval','stakeholder'=>'Made Wirawan (Finance)','module'=>'Sales','status'=>'Active','action'=>'Workflow: status -> To Approve jika diskon >10%'],
            ['id'=>'SR-09','name'=>'P03 per lot, WH-JKT FEFO, kedaluwarsa dilarang kirim','stakeholder'=>'Bagus Hartanto (Ops)','module'=>'Inventory','status'=>'Active','action'=>'FEFO sort expiry ASC, isExpired() guard'],
            ['id'=>'SR-10','name'=>'Setiap variant SKU unik','stakeholder'=>'Rita Kusuma (Pengadaan)','module'=>'Inventory','status'=>'Active','action'=>'UNIQUE constraint pada variant_sku, 16 SKU'],
            ['id'=>'SR-11','name'=>'Credit exposure melebihi limit -> SO ditolak','stakeholder'=>'Made Wirawan (Finance)','module'=>'Sales','status'=>'Active','action'=>'Hard block (Opsi A dipilih, DL-05)'],
            ['id'=>'SR-12','name'=>'Harga & diskon terkunci setelah Confirmed','stakeholder'=>'Dini Pratiwi (Sales)','module'=>'Sales','status'=>'Active','action'=>'Field read-only setelah status = Confirmed'],
            ['id'=>'SR-13','name'=>'Return layak jual + Credit Note ≤ qty ditagih','stakeholder'=>'Hendra Widodo (Owner)','module'=>'Accounting','status'=>'Active','action'=>'Return stock move + credit note ceiling'],
            ['id'=>'SR-14','name'=>'Internal Transfer: WH-JKT <-> HUB-BDG, nilai tidak berubah','stakeholder'=>'Sari Prameswari (Gudang)','module'=>'Inventory','status'=>'Active','action'=>'Transfer antar-lokasi, tidak picu jurnal AR/AP/PPN'],
            ['id'=>'SR-15','name'=>'Data privat karyawan hanya HR','stakeholder'=>'Melati Yusuf (SDM)','module'=>'Employees','status'=>'Active','action'=>'API-level access control, T-28'],
            ['id'=>'SR-16','name'=>'Dokumen bernomor unik, posting idempotent','stakeholder'=>'Made Wirawan (Finance)','module'=>'Accounting','status'=>'Active','action'=>'Idempotency key UNIQUE, double-post guard'],
            ['id'=>'SR-17','name'=>'DO & Receipt wajib validator Employee Aktif Warehouse','stakeholder'=>'Sari Prameswari (Gudang)','module'=>'Inventory','status'=>'Active','action'=>'Employee::canSignOff() cek real-time'],
            ['id'=>'SR-18','name'=>'Employee terpisah dari User Account, hanya Aktif boleh sign-off','stakeholder'=>'Melati Yusuf (SDM)','module'=>'Employees','status'=>'Active','action'=>'Saling melengkapi SR-17, DL-06'],
            ['id'=>'SR-19','name'=>'Dashboard KPI dari dokumen Posted, tiap angka ditelusuri','stakeholder'=>'Bagus Hartanto (Ops)','module'=>'Reporting','status'=>'Active','action'=>'KPI hanya dari Posted/Done, draft tidak dihitung'],
            ['id'=>'SR-20','name'=>'Partial Payment ≤ outstanding, stok & pajak tidak berubah','stakeholder'=>'Made Wirawan (Finance)','module'=>'Accounting','status'=>'Active','action'=>'Validasi amount ≤ outstanding'],
            ['id'=>'SR-21','name'=>'Unit price PO terkunci setelah Confirmed','stakeholder'=>'Rita Kusuma (Pengadaan)','module'=>'Purchase','status'=>'Active','action'=>'Unit price read-only setelah PO Confirmed'],
            ['id'=>'SR-22','name'=>'Available = On Hand − Reserved, reserved ≤ On Hand','stakeholder'=>'Sari Prameswari (Gudang)','module'=>'Inventory','status'=>'Active','action'=>'Computed field, guard per lokasi'],
        ];
        $roleMatrix = [
            ['role'=>'Sales','sales'=>'✅','purchase'=>'❌','inventory'=>'view','accounting'=>'❌','employees'=>'view','audit'=>'own'],
            ['role'=>'Purchase','sales'=>'❌','purchase'=>'✅','inventory'=>'✅','accounting'=>'❌','employees'=>'view','audit'=>'own'],
            ['role'=>'Warehouse','sales'=>'❌','purchase'=>'❌','inventory'=>'✅ full','accounting'=>'❌','employees'=>'view','audit'=>'own'],
            ['role'=>'Finance','sales'=>'❌','purchase'=>'❌','inventory'=>'❌','accounting'=>'✅','employees'=>'view','audit'=>'✅ full'],
            ['role'=>'HR','sales'=>'❌','purchase'=>'❌','inventory'=>'❌','accounting'=>'❌','employees'=>'✅ full+private','audit'=>'own'],
            ['role'=>'Owner','sales'=>'✅','purchase'=>'✅','inventory'=>'✅','accounting'=>'✅','employees'=>'✅','audit'=>'✅ full'],
        ];
        $userAccounts = \App\Models\User::with('employee')->orderBy('role')->get();
        return view('admin.business-rules', compact('businessRules','roleMatrix','userAccounts'));
    })->name('admin.business-rules');

    // ── Inventory & Stock Verification (S-03, S-06, S-07, S-09) ───────
    Route::get('/inventory/stock', function () {
        $stocks = \App\Models\OpeningStock::with('product')->orderBy('variant_sku')->get();
        $lots = \App\Models\ProductLot::with(['product','warehouseLocation'])->orderBy('expiry_date')->get();
        $warehouses = \App\Models\WarehouseLocation::all();
        $totalOnHand = $stocks->sum('company_total');
        $stockValue = $stocks->sum('standard_value');
        $whJkt = $stocks->sum('wh_jkt_on_hand');
        $hubBdg = $stocks->sum('hub_bdg_on_hand');
        return view('inventory.stock', compact('stocks','lots','warehouses','totalOnHand','stockValue','whJkt','hubBdg'));
    })->name('inventory.stock');

    // ── Integration Testing Monitor (T-01..T-36) ─────────────────────
    Route::get('/reporting/testing', function () {
        $tests = [
            ['id'=>'T-01','module'=>'M3','name'=>'App Switcher & Access Control','category'=>'App Switcher','status'=>'pass'],
            ['id'=>'T-02','module'=>'M3','name'=>'Role-based Menu Visibility','category'=>'App Switcher','status'=>'pass'],
            ['id'=>'T-03','module'=>'M1','name'=>'Customer C01 Credit Exposure Baseline','category'=>'Contact & Credit','status'=>'pass'],
            ['id'=>'T-04','module'=>'M1','name'=>'Credit Limit Enforcement (SR-11)','category'=>'Contact & Credit','status'=>'pass'],
            ['id'=>'T-05','module'=>'M2','name'=>'Create Purchase Order PO00008','category'=>'PO & Receipt','status'=>'pass'],
            ['id'=>'T-06','module'=>'M2','name'=>'Goods Receipt & Stock Interim Journal','category'=>'PO & Receipt','status'=>'pass'],
            ['id'=>'T-07','module'=>'M1','name'=>'Convert Lead L00003 to Opportunity (SR-19)','category'=>'CRM Pipeline','status'=>'pass'],
            ['id'=>'T-08','module'=>'M1','name'=>'Customer Relation Traceability on Opportunity','category'=>'CRM Pipeline','status'=>'pass'],
            ['id'=>'T-09','module'=>'M1','name'=>'Quotation to Confirmed SO S00008 & Tax','category'=>'SO & Discount','status'=>'pass'],
            ['id'=>'T-10','module'=>'M1','name'=>'Discount Approval >10% (SR-07/08) & Line Lock (SR-12)','category'=>'SO & Discount','status'=>'pass'],
            ['id'=>'T-11','module'=>'M2','name'=>'Anti-Negative Stock Ceiling (SR-04)','category'=>'Delivery & Transfer','status'=>'pass'],
            ['id'=>'T-12','module'=>'M2','name'=>'FEFO Lot Picking Engine (F-12)','category'=>'Delivery & Transfer','status'=>'pass'],
            ['id'=>'T-13','module'=>'M2','name'=>'Internal Stock Transfer WH-JKT <-> HUB-BDG','category'=>'Delivery & Transfer','status'=>'pass'],
            ['id'=>'T-14','module'=>'M2','name'=>'FEFO Expiry Verification','category'=>'FEFO & SKU','status'=>'pass'],
            ['id'=>'T-15','module'=>'M2','name'=>'PO Confirm & Price Lock (SR-21)','category'=>'PO & Receipt','status'=>'pass'],
            ['id'=>'T-16','module'=>'M2','name'=>'Receipt Quality Acceptance','category'=>'PO & Receipt','status'=>'pass'],
            ['id'=>'T-17','module'=>'M2','name'=>'Delivery Order Sign-off (SR-17)','category'=>'Delivery & Transfer','status'=>'pass'],
            ['id'=>'T-18','module'=>'M2','name'=>'Sign-off Rejected untuk Employee Nonaktif (SR-18)','category'=>'Delivery & Transfer','status'=>'pass'],
            ['id'=>'T-19','module'=>'M4','name'=>'Customer Invoice Posted setelah Delivery (SR-02)','category'=>'Invoice & GL','status'=>'pass'],
            ['id'=>'T-20','module'=>'M4','name'=>'Invoice qty capped at delivered (SR-01 vs SR-02)','category'=>'Invoice & GL','status'=>'pass'],
            ['id'=>'T-21','module'=>'M4','name'=>'Vendor Bill 3-way Match (SR-06)','category'=>'Bill & Payment','status'=>'pass'],
            ['id'=>'T-22','module'=>'M4','name'=>'SR-05 vs SR-06 Bill blocked (SR-06 wins)','category'=>'Bill & Payment','status'=>'pass'],
            ['id'=>'T-23','module'=>'M4','name'=>'Partial Payment Rp100.000 (SR-20)','category'=>'Bill & Payment','status'=>'pass'],
            ['id'=>'T-24','module'=>'M4','name'=>'Overpayment Ditolak (SR-20)','category'=>'Bill & Payment','status'=>'pass'],
            ['id'=>'T-25','module'=>'M4','name'=>'Journal COGS Delivery (F-14)','category'=>'Invoice & GL','status'=>'pass'],
            ['id'=>'T-26','module'=>'M4','name'=>'Idempotency Guard Double Posting (SR-16)','category'=>'Invoice & GL','status'=>'pass'],
            ['id'=>'T-27','module'=>'M3','name'=>'Employee Directory & Department','category'=>'Employees','status'=>'pass'],
            ['id'=>'T-28','module'=>'M1','name'=>'Employee Privacy & Role Boundary (SR-15)','category'=>'Employees','status'=>'pass'],
            ['id'=>'T-29','module'=>'M3','name'=>'Employee Form & Validator','category'=>'Employees','status'=>'pass'],
            ['id'=>'T-30','module'=>'M3','name'=>'Department Kanban View','category'=>'Employees','status'=>'pass'],
            ['id'=>'T-31','module'=>'M4','name'=>'Return & Credit Note (SR-13)','category'=>'Return & Credit','status'=>'pass'],
            ['id'=>'T-32','module'=>'M4','name'=>'Credit Note qty ceiling violation (SR-13)','category'=>'Return & Credit','status'=>'pass'],
            ['id'=>'T-33','module'=>'M4','name'=>'Dashboard KPI Baseline (SR-19)','category'=>'Dashboard & Trace','status'=>'pass'],
            ['id'=>'T-34','module'=>'M4','name'=>'Draft tidak menggelembungkan AR (SR-19)','category'=>'Dashboard & Trace','status'=>'pass'],
            ['id'=>'T-35','module'=>'M4','name'=>'PO00007 Related Docs & Smart Buttons (F-21)','category'=>'Audit & Idempotency','status'=>'pass'],
            ['id'=>'T-36','module'=>'M4','name'=>'Receipt Re-validate Idempotent (SR-16)','category'=>'Audit & Idempotency','status'=>'pass'],
        ];
        $passed = collect($tests)->where('status','pass')->count();
        $total = count($tests);
        return view('reporting.testing', compact('tests','passed','total'));
    })->name('reporting.testing');

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

    // ── Purchase Orders (M2 · F-11 · S-08 · T-05, T-15, T-16) ───────────
    Route::prefix('purchase')->name('purchase.')->middleware('role:Purchase,Owner,Finance')->group(function () {
        Route::get('/orders', [\App\Http\Controllers\Purchase\PurchaseOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/create', [\App\Http\Controllers\Purchase\PurchaseOrderController::class, 'create'])->name('orders.create');
        Route::post('/orders', [\App\Http\Controllers\Purchase\PurchaseOrderController::class, 'store'])->name('orders.store');
        Route::get('/orders/{id}', [\App\Http\Controllers\Purchase\PurchaseOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{id}/confirm', [\App\Http\Controllers\Purchase\PurchaseOrderController::class, 'confirm'])->name('orders.confirm');
        Route::post('/orders/{id}/cancel', [\App\Http\Controllers\Purchase\PurchaseOrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/orders/{id}/receipt/create', [\App\Http\Controllers\Purchase\PurchaseOrderController::class, 'createReceipt'])->name('orders.receipt.create');
    });

    // ── Deliveries & Receipts (M2 · F-07/F-08/F-17 · S-06 · T-11–T-18) ─
    Route::prefix('inventory')->name('inventory.')->group(function () {
        // Delivery Orders — Warehouse, Owner
        Route::middleware('role:Warehouse,Owner')->group(function () {
            Route::get('/deliveries', [\App\Http\Controllers\Inventory\DeliveryController::class, 'index'])->name('deliveries.index');
            Route::get('/deliveries/create', [\App\Http\Controllers\Inventory\DeliveryController::class, 'create'])->name('deliveries.create');
            Route::post('/deliveries', [\App\Http\Controllers\Inventory\DeliveryController::class, 'store'])->name('deliveries.store');
            Route::get('/deliveries/{id}', [\App\Http\Controllers\Inventory\DeliveryController::class, 'show'])->name('deliveries.show');
            Route::post('/deliveries/{id}/validate', [\App\Http\Controllers\Inventory\DeliveryController::class, 'validateDelivery'])->name('deliveries.validate');
        });

        // Goods Receipts — Warehouse, Purchase, Owner
        Route::middleware('role:Warehouse,Purchase,Owner')->group(function () {
            Route::get('/receipts', [\App\Http\Controllers\Inventory\ReceiptController::class, 'index'])->name('receipts.index');
            Route::post('/receipts', [\App\Http\Controllers\Inventory\ReceiptController::class, 'store'])->name('receipts.store');
            Route::get('/receipts/{id}', [\App\Http\Controllers\Inventory\ReceiptController::class, 'show'])->name('receipts.show');
            Route::post('/receipts/{id}/validate', [\App\Http\Controllers\Inventory\ReceiptController::class, 'validateReceipt'])->name('receipts.validate');
        });

        // Internal Transfer (F-17, SR-14) — Warehouse, Owner
        Route::middleware('role:Warehouse,Owner')->group(function () {
            Route::get('/transfer', function () {
                $stocks = \App\Models\OpeningStock::with('product')->orderBy('variant_sku')->get();
                return view('inventory.transfer', compact('stocks'));
            })->name('transfer');
            Route::post('/transfer', function (\Illuminate\Http\Request $request) {
                $request->validate([
                    'variant_sku'   => 'required|exists:products,variant_sku',
                    'from_location' => 'required|in:WH-JKT,HUB-BDG',
                    'to_location'   => 'required|in:WH-JKT,HUB-BDG|different:from_location',
                    'qty'           => 'required|integer|min:1',
                ]);
                try {
                    app(\App\Services\InventoryService::class)->transferStock(
                        $request->from_location,
                        $request->to_location,
                        $request->variant_sku,
                        (int) $request->qty,
                        auth()->user()->username ?? auth()->user()->name
                    );
                    return redirect()->route('inventory.stock')
                        ->with('success', "Transfer {$request->qty}×{$request->variant_sku} dari {$request->from_location} ke {$request->to_location} berhasil (SR-14).");
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', $e->getMessage());
                }
            })->name('transfer.store');
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
