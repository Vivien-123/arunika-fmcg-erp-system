<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * PurchaseOrderController — RFQ → Purchase Order lifecycle (M02)
 *
 * Features : F-07 (RFQ → PO), F-08 (Goods Receipt)
 * Screens  : S-08 (PO List), S-09 (PO Form)
 * Tests    : T-05 (PO00008 creation), T-06 (receipt + stock interim)
 * Roles    : Purchase, Owner
 *
 * TODO M02: Implement all methods below using PurchaseService
 */
class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        // TODO: List view — RFQ and Confirmed POs with status filter
    }

    public function create()
    {
        // TODO: RFQ form — load Vendor list + vendor_pricelist lookup
    }

    public function store(Request $request)
    {
        // TODO: Save RFQ draft — call PurchaseService::createRfq()
    }

    public function show(string $id)
    {
        // TODO: PO form — lines, status bar, related Receipt count, Bill count smart buttons
    }

    public function confirm(Request $request, string $id)
    {
        // TODO: SR-21 — lock unit price from VendorPricelist at confirmation
        //       AuditLog::record('purchase_order', $id, 'confirmed', ...)
    }

    public function cancel(Request $request, string $id)
    {
        // TODO: AuditLog::record('purchase_order', $id, 'cancelled', ...)
    }
}
