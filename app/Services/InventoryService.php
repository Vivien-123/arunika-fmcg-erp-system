<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\OpeningStock;
use App\Models\ProductLot;
use App\Models\Employee;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

/**
 * InventoryService — M02 Inventory Business Logic Layer
 *
 * Mirrors purchase_inventory_service.js for the Laravel web UI.
 *
 * SR-03 : Perpetual standard cost; all stock moves generate JournalEntries.
 * SR-04 : Anti-negative stock — throws before any decrement attempt.
 * SR-07 : FEFO lot selection on delivery picking.
 * SR-08 : Multi-location stock tracking (WH-JKT and HUB-BDG).
 * SR-17 : Only Active Warehouse employees with a mapped user may sign off.
 * SR-18 : Employee status Nonaktif blocks sign-off immediately.
 *
 * TODO M02: Implement each method stub below.
 *           Reference purchase_inventory_service.js for the JS logic to port.
 */
class InventoryService
{
    // ── Receipt ───────────────────────────────────────────────────────────

    /**
     * Process a validated Goods Receipt.
     *
     * Steps:
     *   1. SR-17/18: validate Employee::canSignOff() for the signing employee
     *   2. Increment opening_stock on_hand for the receiving location
     *   3. Create lot records if variant is lot-tracked (SR-07)
     *   4. Post Stock Interim Journal: Dr 1350 / Cr 2000 (provisional AP)
     *   5. AuditLog entry
     */
    public function processReceipt(string $receiptId, string $signingEmployeeId, string $actorUser): object
    {
        // TODO: implement
        throw new \RuntimeException('InventoryService::processReceipt() not yet implemented.');
    }

    // ── Delivery ─────────────────────────────────────────────────────────

    /**
     * Process a validated Delivery Order.
     *
     * Steps:
     *   1. SR-17/18: validate Employee::canSignOff()
     *   2. SR-07: for lot-tracked SKUs, pick using ProductLot::fefoLotsFor()
     *   3. SR-04: OpeningStock::reserve() / decrement — throws if insufficient
     *   4. Post COGS Journal: Dr 5100 / Cr 1300 (standard cost × qty)
     *   5. AuditLog entry
     */
    public function processDelivery(string $deliveryId, string $signingEmployeeId, string $actorUser): object
    {
        // TODO: implement
        throw new \RuntimeException('InventoryService::processDelivery() not yet implemented.');
    }

    // ── Stock Transfer ────────────────────────────────────────────────────

    /**
     * Transfer stock between locations (WH-JKT ↔ HUB-BDG).
     * Company total on_hand does NOT change — only location split moves.
     * SR-04: source location must have enough on_hand.
     * T-13: WH-JKT −10, HUB-BDG +10, company_total unchanged.
     */
    public function transferStock(string $fromLocation, string $toLocation, string $variantSku, int $qty, string $actorUser): void
    {
        // TODO: implement
        throw new \RuntimeException('InventoryService::transferStock() not yet implemented.');
    }

    // ── FEFO Lot Allocation ───────────────────────────────────────────────

    /**
     * SR-07: Allocate qty from FEFO-sorted lots for a given SKU and location.
     * Returns array of ['lot_id' => ..., 'qty_allocated' => ...] for each lot used.
     * Throws if total available lot qty < requested qty.
     */
    public function allocateFefo(string $variantSku, int $requiredQty, ?string $locationCode = null): array
    {
        $lots = ProductLot::fefoLotsFor($variantSku, $locationCode);
        $remaining = $requiredQty;
        $allocation = [];

        foreach ($lots as $lot) {
            if ($remaining <= 0) break;
            $take = min($lot->qty, $remaining);
            $allocation[] = ['lot_id' => $lot->lot_id, 'qty_allocated' => $take];
            $remaining -= $take;
        }

        if ($remaining > 0) {
            throw new \RuntimeException(
                "SR-04/SR-07: Stok lot tidak cukup untuk {$variantSku}. "
                . "Diminta: {$requiredQty}, tersedia: " . ($requiredQty - $remaining) . "."
            );
        }

        return $allocation;
    }

    // ── COGS Journal ──────────────────────────────────────────────────────

    /**
     * SR-03: Post perpetual COGS journal entry for a stock move.
     * Dr COGS (5100) / Cr Inventory (1300) @ standard cost × qty.
     */
    public function postCogsJournal(string $reference, string $variantSku, int $qty, string $postedBy): JournalEntry
    {
        // TODO: look up standard_cost from OpeningStock, call JournalEntry::buildIdempotencyKey()
        throw new \RuntimeException('InventoryService::postCogsJournal() not yet implemented.');
    }
}
