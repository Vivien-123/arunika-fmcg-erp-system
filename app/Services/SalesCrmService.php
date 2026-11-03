<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\OpeningStock;
use Illuminate\Support\Facades\DB;

/**
 * SalesCrmService — M01 Business Logic Layer
 *
 * Mirrors sales_crm_service.js for the Laravel web UI.
 *
 * SR-07 / SR-08 : Discount > 10% requires Finance approval before confirm.
 * SR-11         : Credit exposure ceiling check on SO confirmation.
 * SR-12         : Lines and price locked permanently after confirmation.
 * SR-19         : Lead → Opportunity requires a customer relation.
 * SR-22         : Stock reservation via OpeningStock::reserve().
 *
 * TODO M01: Implement each method stub below.
 *           Reference sales_crm_service.js for the JS business logic to port.
 */
class SalesCrmService
{
    // ── CRM ───────────────────────────────────────────────────────────────

    /**
     * Create a new Lead.
     * No stock, no accounting impact at this stage.
     */
    public function createLead(array $params): object
    {
        // TODO: insert into leads table, AuditLog::record('lead', ..., 'created', ...)
        throw new \RuntimeException('SalesCrmService::createLead() not yet implemented.');
    }

    /**
     * Convert a Lead to an Opportunity.
     * SR-19: customer relation must be set.
     */
    public function convertToOpportunity(string $leadId, string $actorUser): object
    {
        // TODO: validate partner_id not null (SR-19)
        //       update stage to 'qualified'
        //       AuditLog::record('lead', $leadId, 'converted', ...)
        throw new \RuntimeException('SalesCrmService::convertToOpportunity() not yet implemented.');
    }

    // ── Quotation / Sales Order ────────────────────────────────────────────

    /**
     * Create a new draft Quotation from a customer and line items.
     * Calculates PPN 11% per line: floor(net * 0.11 + 0.5).
     */
    public function createQuotation(array $params): object
    {
        // TODO: insert sales_order (status=draft) + sales_order_lines
        //       AuditLog::record('sales_order', ..., 'created', ...)
        throw new \RuntimeException('SalesCrmService::createQuotation() not yet implemented.');
    }

    /**
     * Confirm a Quotation → Sales Order.
     *
     * Enforcement order:
     *   1. SR-07/08: any line discount > 10% → set status='to_approve', return early
     *   2. SR-11: credit exposure check
     *   3. SR-22: reserve stock via OpeningStock::reserve()
     *   4. SR-12: lock all lines (price + qty become read-only)
     *   5. Audit log on success
     */
    public function confirmSalesOrder(string $soId, string $actorUser): object
    {
        // TODO: implement full SR chain
        throw new \RuntimeException('SalesCrmService::confirmSalesOrder() not yet implemented.');
    }

    /**
     * Finance approves a discount on a To-Approve quotation.
     * SR-08: only Finance/Owner may call this.
     */
    public function approveDiscount(string $soId, string $actorUser): object
    {
        // TODO: set status='confirmed', lock lines SR-12
        //       AuditLog::record('sales_order', $soId, 'discount_approved', ...)
        throw new \RuntimeException('SalesCrmService::approveDiscount() not yet implemented.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Calculate credit exposure for a customer.
     * Exposure = open_ar + unbilled confirmed SO gross totals.
     */
    public function creditExposure(string $customerId): int
    {
        // TODO: query contacts_customers.open_ar + sum of confirmed SO gross amounts not yet invoiced
        throw new \RuntimeException('SalesCrmService::creditExposure() not yet implemented.');
    }

    /**
     * Calculate PPN 11% (effective) on a net amount.
     * floor(net * 0.11 + 0.5) — matches AccountingService::calcTax()
     */
    public function calcTax(int $net): int
    {
        return (int) floor($net * 0.11 + 0.5);
    }

    /**
     * Generate next Sales Order number (S00001 … S99999).
     */
    private function nextSoNumber(): string
    {
        // TODO: query max SO number and increment
        throw new \RuntimeException('SalesCrmService::nextSoNumber() not yet implemented.');
    }
}
