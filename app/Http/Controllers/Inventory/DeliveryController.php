<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * DeliveryController — Delivery Order & FEFO Picking (M02)
 *
 * Features : F-11 (Delivery), F-12 (FEFO lot picking SR-07), F-17 (no negative stock SR-04)
 * Screens  : S-06 sub-view (Delivery Form)
 * Tests    : T-12 (FEFO LOT-TEA-01 first), T-13 (stock transfer), T-18 (sign-off)
 * Roles    : Warehouse, Owner
 *
 * TODO M02: Implement using InventoryService
 */
class DeliveryController extends Controller
{
    public function show(string $id)
    {
        // TODO: Delivery form — line items, lot assignment panel
    }

    public function validate(Request $request, string $id)
    {
        // TODO: SR-07 FEFO lot pick via ProductLot::fefoLotsFor()
        //       SR-04 anti-negative stock check
        //       SR-17/18 Employee::canSignOff()
        //       InventoryService::processDelivery() — stock move + COGS journal
        //       AuditLog::record('delivery', $id, 'validated', ...)
    }
}
