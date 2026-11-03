<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\VendorPricelist;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

/**
 * PurchaseService — M02 Purchase Business Logic Layer
 *
 * Mirrors purchase_inventory_service.js (purchase section) for the Laravel web UI.
 *
 * SR-05 : PO unit price must come from VendorPricelist (or be agreed price).
 * SR-06 : Vendor bill qty capped at accepted receipt qty (3-way match).
 * SR-21 : Unit price locked at PO confirmation — cannot be changed after.
 *
 * TODO M02: Implement each method stub below.
 */
class PurchaseService
{
    /**
     * Create a draft RFQ (Request for Quotation).
     * Lines: vendor_id, variant_sku, qty, unit_price (from pricelist or manual).
     */
    public function createRfq(array $params): object
    {
        // TODO: insert purchase_orders (status=rfq) + purchase_order_lines
        //       AuditLog::record('purchase_order', ..., 'created', ...)
        throw new \RuntimeException('PurchaseService::createRfq() not yet implemented.');
    }

    /**
     * Confirm a RFQ → Purchase Order.
     * SR-21: lock unit price from VendorPricelist at this moment.
     * Lines become read-only after this point.
     */
    public function confirmPurchaseOrder(string $poId, string $actorUser): object
    {
        // TODO: lock lines, set status='confirmed'
        //       AuditLog::record('purchase_order', $poId, 'confirmed', ...)
        throw new \RuntimeException('PurchaseService::confirmPurchaseOrder() not yet implemented.');
    }

    /**
     * Validate 3-way match before creating a Vendor Bill.
     * SR-06: billable qty = accepted_receipt_qty - already_billed_qty.
     * Delegates to AccountingService::validateThreeWayMatch() for detailed violation list.
     */
    public function validateThreeWayMatch(string $poId, string $variantSku, int $orderedQty, int $acceptedQty, int $alreadyBilled, int $requestBillQty): array
    {
        // Delegate to AccountingService — already implemented
        return app(AccountingService::class)->validateThreeWayMatch($poId, $variantSku, $orderedQty, $acceptedQty, $alreadyBilled, $requestBillQty);
    }

    /**
     * Generate next PO number (PO00001 … PO99999).
     */
    private function nextPoNumber(): string
    {
        // TODO: query max PO number and increment
        throw new \RuntimeException('PurchaseService::nextPoNumber() not yet implemented.');
    }
}
