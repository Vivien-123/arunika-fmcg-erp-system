<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AuditController;

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
         ->where('docId', '.*'); // docId may contain slashes or dots
});
