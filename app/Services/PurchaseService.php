<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\VendorPricelist;
use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * PurchaseService — Purchase Business Logic Layer
 *
 * SR-05: PO unit price must come from VendorPricelist (or agreed price).
 * SR-06: Vendor bill qty capped at accepted receipt qty (3-way match).
 * SR-21: Unit price locked at PO confirmation — cannot be changed after.
 */
class PurchaseService
{
    // ── Number sequence ───────────────────────────────────────────────────

    private function nextPoNumber(): string
    {
        $last = DB::table('purchase_orders')
            ->where('id', 'like', 'PO%')
            ->orderByDesc('id')
            ->value('id');
        $num = $last ? ((int) substr($last, 2)) + 1 : 1;
        return 'PO' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    // ── RFQ / PO Creation ─────────────────────────────────────────────────

    /**
     * Create a draft RFQ.
     * params: vendor_id, lines[]{variant_sku, quantity, unit_price?}, created_by
     */
    public function createRfq(array $params): PurchaseOrder
    {
        return DB::transaction(function () use ($params) {
            $vendor = Vendor::findOrFail($params['vendor_id']);
            $poId   = $this->nextPoNumber();
            $netTotal = 0;
            $taxTotal = 0;

            $po = PurchaseOrder::create([
                'id'           => $poId,
                'vendor_id'    => $vendor->id,
                'vendor_name'  => $vendor->name,
                'status'       => 'rfq',
                'is_locked'    => false,
                'payment_terms'=> $vendor->payment_terms,
                'order_date'   => now()->toDateString(),
                'created_by'   => $params['created_by'],
            ]);

            foreach ($params['lines'] as $line) {
                $product = Product::where('variant_sku', $line['variant_sku'])->firstOrFail();
                // SR-21: get price from pricelist if not provided
                $unitPrice = isset($line['unit_price']) && $line['unit_price'] > 0
                    ? (int) $line['unit_price']
                    : (VendorPricelist::where('vendor_id', $vendor->id)
                        ->where('variant_sku', $line['variant_sku'])->value('unit_price')
                        ?? $product->cost);
                $qty   = (int) $line['quantity'];
                $net   = $qty * $unitPrice;
                $tax   = (int) round($net * 0.11);
                $gross = $net + $tax;
                $netTotal += $net;
                $taxTotal += $tax;

                PurchaseOrderLine::create([
                    'purchase_order_id' => $poId,
                    'product_id'   => $product->id,
                    'variant_sku'  => $product->variant_sku,
                    'product_name' => $product->name,
                    'unit'         => $product->unit,
                    'quantity'     => $qty,
                    'unit_price'   => $unitPrice,
                    'is_locked'    => false,
                    'net_amount'   => $net,
                    'tax_amount'   => $tax,
                    'gross_amount' => $gross,
                ]);
            }

            $po->update([
                'net_amount'   => $netTotal,
                'tax_amount'   => $taxTotal,
                'gross_amount' => $netTotal + $taxTotal,
            ]);

            AuditLog::create([
                'document_type' => 'purchase_order',
                'document_id'   => $poId,
                'action'        => 'created',
                'actor_user'    => $params['created_by'],
                'description'   => "RFQ {$poId} dibuat untuk vendor {$vendor->name}.",
                'acted_at'      => now(),
            ]);

            return $po->fresh(['lines']);
        });
    }

    /**
     * Confirm RFQ → Purchase Order.
     * SR-21: lock unit_price from VendorPricelist at this moment.
     */
    public function confirmPurchaseOrder(string $poId, string $actorUser): PurchaseOrder
    {
        return DB::transaction(function () use ($poId, $actorUser) {
            $po = PurchaseOrder::with('lines')->findOrFail($poId);

            if ($po->status !== 'rfq') {
                throw new \RuntimeException("PO {$poId} bukan RFQ (status: {$po->status}).");
            }

            // SR-21: lock each line's unit_price from pricelist at this moment
            foreach ($po->lines as $line) {
                $listedPrice = VendorPricelist::where('vendor_id', $po->vendor_id)
                    ->where('variant_sku', $line->variant_sku)
                    ->value('unit_price');
                $lockedPrice = $listedPrice ?? $line->unit_price;
                $net   = $line->quantity * $lockedPrice;
                $tax   = (int) round($net * 0.11);
                $line->update([
                    'unit_price'   => $lockedPrice,
                    'is_locked'    => true,
                    'net_amount'   => $net,
                    'tax_amount'   => $tax,
                    'gross_amount' => $net + $tax,
                ]);
            }

            $po->refresh();
            $net = $po->lines->sum('net_amount');
            $tax = $po->lines->sum('tax_amount');
            $po->update([
                'status'       => 'confirmed',
                'is_locked'    => true,
                'net_amount'   => $net,
                'tax_amount'   => $tax,
                'gross_amount' => $net + $tax,
                'confirmed_by' => $actorUser,
                'confirmed_at' => now(),
            ]);

            AuditLog::create([
                'document_type' => 'purchase_order',
                'document_id'   => $poId,
                'action'        => 'confirmed',
                'actor_user'    => $actorUser,
                'description'   => "PO {$poId} dikonfirmasi, harga terkunci (SR-21).",
                'new_values'    => json_encode(['status' => 'confirmed', 'is_locked' => true]),
                'acted_at'      => now(),
            ]);

            return $po->fresh(['lines']);
        });
    }

    /**
     * SR-06: Validate 3-way match for billing.
     * Returns ['ok' => bool, 'billable_qty' => int, 'violations' => array]
     */
    public function validateThreeWayMatch(
        string $poId,
        string $variantSku,
        int $orderedQty,
        int $acceptedQty,
        int $alreadyBilled,
        int $requestBillQty
    ): array {
        $violations = [];
        $billable   = max(0, $acceptedQty - $alreadyBilled);

        if ($requestBillQty > $billable) {
            $violations[] = "SR-06: Qty tagihan ({$requestBillQty}) melebihi accepted yang belum ditagih ({$billable}).";
        }
        if ($acceptedQty > $orderedQty) {
            $violations[] = "SR-06: Accepted qty ({$acceptedQty}) melebihi ordered qty ({$orderedQty}).";
        }

        return [
            'ok'           => empty($violations),
            'billable_qty' => $billable,
            'violations'   => $violations,
        ];
    }
}
