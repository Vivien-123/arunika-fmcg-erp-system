<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * SalesOrderController — Quotation → Sales Order lifecycle (M01)
 *
 * Features : F-05 (Quotation → SO), F-06 (Discount approval), F-10 (Credit limit)
 * Screens  : S-05 (Quotation List), S-06 (SO Form), S-07 (SO Confirm)
 * Tests    : T-03 T-04 (credit), T-09 (SO S00008), T-10 (discount SR-07/08)
 * Roles    : confirm → Sales | approve_discount → Finance | view → Sales,Owner
 *
 * TODO M01: Implement all methods below using SalesCrmService
 */
class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        // TODO: List view — filter by status, customer, date
    }

    public function create()
    {
        // TODO: Blank quotation form — load Customer list + Product catalog
    }

    public function store(Request $request)
    {
        // TODO: Save draft quotation — call SalesCrmService::createQuotation()
    }

    public function show(string $id)
    {
        // TODO: Form view — lines, status bar, smart buttons (delivery count, invoice count)
    }

    public function confirm(Request $request, string $id)
    {
        // TODO: SR-11 credit check → SR-07/08 discount check → lock prices SR-12
        //       Reserve stock via OpeningStock::reserve()
        //       AuditLog::record('sales_order', $id, 'confirmed', ...)
    }

    public function approveDiscount(Request $request, string $id)
    {
        // TODO: Finance role only — SR-08 approval gate
        //       AuditLog::record('sales_order', $id, 'discount_approved', ...)
    }

    public function cancel(Request $request, string $id)
    {
        // TODO: Release reservation via OpeningStock::releaseReservation()
        //       AuditLog::record('sales_order', $id, 'cancelled', ...)
    }
}
