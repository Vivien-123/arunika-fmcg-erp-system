<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\OpeningStock;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderLine;
use Illuminate\Support\Facades\DB;

/**
 * SalesCrmService — M01 Business Logic Layer
 *
 * SR-07 / SR-08 : Discount > 10% requires Finance approval before confirm.
 * SR-11         : Credit exposure = open_ar + confirmed unbilled SO gross.
 * SR-12         : Lines locked (is_locked=true) after confirmation.
 * SR-19         : Lead → Opportunity requires partner relation.
 * SR-22         : Reservation via OpeningStock::reserve() — never reduces On Hand.
 */
class SalesCrmService
{
    // ══════════════════════════════════════════════════════════════════════
    //  CRM
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Create a new Lead.
     * No stock, no accounting impact at this stage.
     *
     * @param  array{partner_id?: string, expected_revenue?: int, ...} $params
     */
    public function createLead(array $params): Lead
    {
        $lead = Lead::create([
            'id'               => Lead::nextId(),
            'partner_id'       => $params['partner_id'] ?? null,
            'partner_name'     => $params['partner_name'] ?? null,
            'stage'            => 'new',
            'salesperson_id'   => $params['salesperson_id'] ?? null,
            'salesperson_name' => $params['salesperson_name'] ?? null,
            'expected_revenue' => $params['expected_revenue'] ?? 0,
            'notes'            => $params['notes'] ?? null,
            'created_by'       => $params['created_by'] ?? '',
        ]);

        AuditLog::record(
            'lead', $lead->id, 'created',
            $params['created_by'] ?? 'system',
            "Lead {$lead->id} created. Customer: {$lead->partner_name}."
        );

        return $lead;
    }

    /**
     * Convert a Lead to an Opportunity (stage → qualified).
     *
     * SR-19: partner_id must be set; throws if missing.
     */
    public function convertToOpportunity(string $leadId, string $actorUser): Lead
    {
        $lead = Lead::findOrFail($leadId);

        // SR-19: customer relation is mandatory
        if (empty($lead->partner_id)) {
            throw new \RuntimeException(
                "SR-19 Enforcement: Customer relation cannot be empty when converting Lead {$leadId} to Opportunity."
            );
        }

        AuditLog::record(
            'lead', $leadId, 'convert_to_opportunity',
            $actorUser,
            "Lead {$leadId} converted to Opportunity. Stage: new → qualified. Customer: {$lead->partner_name}.",
            null,
            ['stage' => $lead->stage],
            ['stage' => 'qualified']
        );

        $lead->update(['stage' => 'qualified']);
        return $lead->fresh();
    }

    /**
     * SR-19: Block deletion of customer relation on a Qualified+ Opportunity.
     */
    public function removeCustomerRelation(string $leadId, string $actorUser): void
    {
        $lead = Lead::findOrFail($leadId);

        if ($lead->isOpportunity()) {
            AuditLog::record(
                'lead', $leadId, 'reject_delete_customer',
                $actorUser,
                "SR-19 Violation: Attempt to remove customer relation on Opportunity {$leadId} blocked."
            );
            throw new \RuntimeException(
                "SR-19 Enforcement: Customer relation cannot be deleted on Opportunity {$leadId}."
            );
        }

        $lead->update(['partner_id' => null, 'partner_name' => null]);
        AuditLog::record('lead', $leadId, 'customer_removed', $actorUser,
            "Customer relation removed from Lead {$leadId} (still in 'new' stage).");
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Quotation / Sales Order
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Create a new draft Quotation.
     *
     * Calculates PPN 11% per line: floor(net × 0.11 + 0.5).
     *
     * @param  array{
     *   customer_id: string,
     *   created_by: string,
     *   payment_terms?: string,
     *   lines: array<array{product_id:string, variant_sku:string, product_name:string,
     *                       unit:string, quantity:int, unit_price:int, discount_percent?:float}>
     * } $params
     */
    public function createQuotation(array $params): SalesOrder
    {
        $customer = Customer::findOrFail($params['customer_id']);

        $soId = SalesOrder::nextId();

        return DB::transaction(function () use ($params, $customer, $soId) {
            $totalUntaxed = 0;
            $totalTax     = 0;
            $totalGross   = 0;

            $so = SalesOrder::create([
                'id'            => $soId,
                'customer_id'   => $customer->id,
                'customer_name' => $customer->name,
                'status'        => 'draft',
                'discount_approval_status' => 'not_required',
                'payment_terms' => $params['payment_terms'] ?? $customer->payment_terms,
                'order_date'    => now()->toDateString(),
                'created_by'    => $params['created_by'],
                'lead_id'       => $params['lead_id'] ?? null,
            ]);

            foreach ($params['lines'] as $lineData) {
                $disc    = (float) ($lineData['discount_percent'] ?? 0.00);
                $qty     = (int) $lineData['quantity'];
                $price   = (int) $lineData['unit_price'];
                $net     = (int) round($qty * $price * (1 - $disc / 100));
                $tax     = $this->calcTax($net);
                $gross   = $net + $tax;

                SalesOrderLine::create([
                    'sales_order_id'  => $so->id,
                    'product_id'      => $lineData['product_id'],
                    'variant_sku'     => $lineData['variant_sku'],
                    'product_name'    => $lineData['product_name'],
                    'unit'            => $lineData['unit'],
                    'quantity'        => $qty,
                    'unit_price'      => $price,
                    'discount_percent'=> $disc,
                    'is_locked'       => false,
                    'net_amount'      => $net,
                    'tax_amount'      => $tax,
                    'gross_amount'    => $gross,
                    'source_location' => $lineData['source_location'] ?? 'WH-JKT',
                ]);

                $totalUntaxed += $net;
                $totalTax     += $tax;
                $totalGross   += $gross;
            }

            $so->update([
                'untaxed_amount' => $totalUntaxed,
                'tax_amount'     => $totalTax,
                'gross_amount'   => $totalGross,
            ]);

            AuditLog::record(
                'sales_order', $so->id, 'created',
                $params['created_by'],
                "Quotation {$so->id} created for {$customer->name}. Lines: " . count($params['lines']) . ". Gross: Rp{$totalGross}.",
                null, [], ['status' => 'draft', 'gross_amount' => $totalGross]
            );

            return $so->fresh(['lines']);
        });
    }

    /**
     * Confirm a Quotation → Sales Order.
     *
     * Enforcement chain (in order):
     *   1. SR-07/08 : any line discount > 10% → status='to_approve', return early
     *   2. SR-11    : credit exposure check (hard block)
     *   3. SR-22    : reserve stock via OpeningStock::reserve()
     *   4. SR-12    : lock all lines
     *   5. SR-03    : delivery promise date = today + 3 days
     *   6. Audit log
     */
    public function confirmSalesOrder(string $soId, string $actorUser): SalesOrder
    {
        $so = SalesOrder::with(['lines', 'customer'])->findOrFail($soId);

        if (!$so->isDraft()) {
            throw new \RuntimeException(
                "SR-16 Enforcement: Sales Order {$soId} is already in status '{$so->status}'. Cannot re-confirm."
            );
        }

        // ── 1. SR-07/08: Discount gate ──────────────────────────────────
        if ($so->requiresDiscountApproval()) {
            $so->update([
                'status'                   => 'to_approve',
                'discount_approval_status' => 'pending',
            ]);
            // Lock lines to prevent edits while waiting for approval
            SalesOrderLine::where('sales_order_id', $soId)
                ->update(['is_locked' => true]);

            AuditLog::record(
                'sales_order', $soId, 'discount_approval_requested',
                $actorUser,
                "SO {$soId}: discount > 10% detected. Status → to_approve. Awaiting Finance approval (SR-07/SR-08).",
                null,
                ['status' => 'draft'],
                ['status' => 'to_approve', 'discount_approval_status' => 'pending']
            );

            return $so->fresh(['lines', 'customer']);
        }

        return $this->_doConfirm($so, $actorUser);
    }

    /**
     * Finance approves a discount on a To-Approve quotation.
     * SR-08: only Finance/Owner role may call this.
     */
    public function approveDiscount(string $soId, string $actorUser): SalesOrder
    {
        $so = SalesOrder::with(['lines', 'customer'])->findOrFail($soId);

        if (!$so->isToApprove()) {
            throw new \RuntimeException(
                "SR-08 Enforcement: Sales Order {$soId} is not pending discount approval (status: {$so->status})."
            );
        }

        $so->update([
            'discount_approval_status' => 'approved',
            'discount_approved_by'     => $actorUser,
            'discount_approved_at'     => now(),
            'status'                   => 'draft', // unlock for _doConfirm
        ]);

        // Reload and complete confirmation
        $so = $so->fresh(['lines', 'customer']);
        return $this->_doConfirm($so, $actorUser);
    }

    /**
     * Finance rejects a discount request → SO back to draft, lines unlocked.
     */
    public function rejectDiscount(string $soId, string $actorUser): SalesOrder
    {
        $so = SalesOrder::findOrFail($soId);

        if (!$so->isToApprove()) {
            throw new \RuntimeException(
                "SR-08 Enforcement: Sales Order {$soId} is not pending discount approval."
            );
        }

        $so->update([
            'status'                   => 'draft',
            'discount_approval_status' => 'rejected',
        ]);
        SalesOrderLine::where('sales_order_id', $soId)
            ->update(['is_locked' => false]);

        AuditLog::record(
            'sales_order', $soId, 'discount_rejected',
            $actorUser,
            "SO {$soId}: discount rejected by Finance. Status → draft. Lines unlocked.",
            null,
            ['status' => 'to_approve'],
            ['status' => 'draft', 'discount_approval_status' => 'rejected']
        );

        return $so->fresh(['lines']);
    }

    /**
     * Cancel a confirmed Sales Order and release stock reservations.
     */
    public function cancelSalesOrder(string $soId, string $actorUser, string $reason = ''): SalesOrder
    {
        $so = SalesOrder::with('lines')->findOrFail($soId);

        if ($so->isCancelled()) {
            throw new \RuntimeException("SR-16 Enforcement: SO {$soId} is already cancelled.");
        }

        DB::transaction(function () use ($so, $soId, $actorUser, $reason) {
            // Release stock reservations (SR-22)
            foreach ($so->lines as $line) {
                if ($line->reserved_qty > 0) {
                    $stock = OpeningStock::find($line->variant_sku);
                    if ($stock) {
                        $stock->releaseReservation($line->reserved_qty);
                    }
                    $line->update(['reserved_qty' => 0]);
                }
            }

            // Unlock lines
            SalesOrderLine::where('sales_order_id', $soId)
                ->update(['is_locked' => false]);

            $so->update(['status' => 'cancelled']);
        });

        AuditLog::record(
            'sales_order', $soId, 'cancelled',
            $actorUser,
            "SO {$soId} cancelled. Reason: {$reason}. Stock reservations released.",
            null,
            ['status' => $so->status],
            ['status' => 'cancelled']
        );

        return $so->fresh(['lines']);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Credit Exposure (SR-11)
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Current credit exposure for a customer.
     * Exposure = open_ar + Σ gross of confirmed SOs not yet fully invoiced.
     */
    public function creditExposure(string $customerId): int
    {
        $customer = Customer::findOrFail($customerId);

        // Sum gross of confirmed SOs that still have uninvoiced qty
        $unbilledSoGross = SalesOrder::where('customer_id', $customerId)
            ->whereIn('status', ['confirmed', 'done'])
            ->join('sales_order_lines', 'sales_orders.id', '=', 'sales_order_lines.sales_order_id')
            ->where('sales_order_lines.invoiced_qty', '<', DB::raw('sales_order_lines.quantity'))
            ->selectRaw('SUM(sales_order_lines.gross_amount) as total')
            ->value('total') ?? 0;

        return (int) $customer->open_ar + (int) $unbilledSoGross;
    }

    /**
     * SR-11: Would confirming $newOrderGross breach this customer's credit limit?
     */
    public function wouldBreachCreditLimit(string $customerId, int $newOrderGross): bool
    {
        return ($this->creditExposure($customerId) + $newOrderGross) > Customer::findOrFail($customerId)->credit_limit;
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Helpers
    // ══════════════════════════════════════════════════════════════════════

    /**
     * PPN 11% (effective) on a net amount.
     * floor(net × 0.11 + 0.5) — half-up rounding, per Handout §4.
     */
    public function calcTax(int $net): int
    {
        return (int) floor($net * 0.11 + 0.5);
    }

    // ══════════════════════════════════════════════════════════════════════
    //  Internal helpers
    // ══════════════════════════════════════════════════════════════════════

    /**
     * Internal: execute the actual confirmation after all guards pass.
     * Called by confirmSalesOrder() and approveDiscount().
     */
    private function _doConfirm(SalesOrder $so, string $actorUser): SalesOrder
    {
        // ── SR-11: Credit limit check ────────────────────────────────────
        $exposure = $this->creditExposure($so->customer_id);
        $limit    = $so->customer->credit_limit;

        if (($exposure + $so->gross_amount) > $limit) {
            $projected = $exposure + $so->gross_amount;
            AuditLog::record(
                'sales_order', $so->id, 'credit_limit_exceeded',
                $actorUser,
                "SR-11 Enforcement: SO {$so->id} rejected. Projected exposure Rp{$projected} > limit Rp{$limit}."
            );
            throw new \RuntimeException(
                "SR-11 Enforcement: Credit limit exceeded. Projected exposure Rp{$projected} > limit Rp{$limit} for customer {$so->customer_id}."
            );
        }

        return DB::transaction(function () use ($so, $actorUser) {
            $reservationErrors = [];

            // ── SR-22: Reserve stock per line ────────────────────────────
            foreach ($so->lines as $line) {
                $stock = OpeningStock::find($line->variant_sku);

                if (!$stock) {
                    $reservationErrors[] = "SKU {$line->variant_sku} not found in opening_stock.";
                    continue;
                }

                // If available < qty → reserve what's possible (rest becomes backorder, SR-03)
                $toReserve = min($line->quantity, $stock->availableQty());

                if ($toReserve > 0) {
                    $stock->reserve($toReserve);
                    $line->update(['reserved_qty' => $toReserve]);
                }
                // Note: if $toReserve < $line->quantity, the gap will become a backorder DO
            }

            // ── SR-12: Lock all lines ─────────────────────────────────────
            SalesOrderLine::where('sales_order_id', $so->id)
                ->update(['is_locked' => true]);

            // ── SR-03: Delivery promise = today + 3 days ──────────────────
            $so->update([
                'status'              => 'confirmed',
                'confirmed_by'        => $actorUser,
                'confirmed_at'        => now(),
                'delivery_promise_date' => now()->addDays(3)->toDateString(),
            ]);

            $reservationNote = empty($reservationErrors)
                ? 'All stock reserved.'
                : 'Partial reservation: ' . implode(' ', $reservationErrors);

            AuditLog::record(
                'sales_order', $so->id, 'confirmed',
                $actorUser,
                "SO {$so->id} confirmed for {$so->customer_name}. Net Rp{$so->untaxed_amount}, PPN Rp{$so->tax_amount}, Gross Rp{$so->gross_amount}. {$reservationNote}",
                null,
                ['status' => 'draft'],
                ['status' => 'confirmed', 'gross_amount' => $so->gross_amount]
            );

            return $so->fresh(['lines', 'customer']);
        });
    }
}
