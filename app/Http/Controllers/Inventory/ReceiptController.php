<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * ReceiptController — Goods Receipt & Inspection (M02)
 *
 * Features : F-08 (Goods Receipt), F-12 (Lot tracking on receipt)
 * Screens  : S-09 sub-view (Receipt Form)
 * Tests    : T-06 (stock interim journal), T-11 (anti-negative SR-04), T-18 (sign-off SR-17/18)
 * Roles    : Warehouse (validate/sign-off), Purchase (create), Owner
 *
 * TODO M02: Implement using InventoryService
 */
class ReceiptController extends Controller
{
    public function show(string $id)
    {
        // TODO: Receipt form — accepted/rejected qty per line
    }

    public function validate(Request $request, string $id)
    {
        // TODO: SR-17/18 — Employee::canSignOff() check before posting
        //       InventoryService::processReceipt() — stock move + interim journal
        //       AuditLog::record('receipt', $id, 'validated', ...)
    }
}
