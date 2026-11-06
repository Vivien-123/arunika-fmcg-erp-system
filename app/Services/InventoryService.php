<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\OpeningStock;
use App\Models\ProductLot;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\StockMove;
use App\Models\Delivery;
use App\Models\DeliveryLine;
use App\Models\Receipt;
use App\Models\ReceiptLine;
use App\Models\SalesOrderLine;
use App\Models\PurchaseOrderLine;
use Illuminate\Support\Facades\DB;

/**
 * InventoryService — Inventory Business Logic Layer
 *
 * SR-03 : Perpetual standard cost; all stock moves generate JournalEntries.
 * SR-04 : Anti-negative stock — throws before any decrement attempt.
 * SR-07 : FEFO lot selection on delivery picking.
 * SR-09 : P03 lot-tracked; expired lots blocked.
 * SR-14 : Internal transfer: company_total unchanged, no AR/AP/tax.
 * SR-16 : Idempotency guard on all posting actions.
 * SR-17 : Only Active Warehouse employees with a mapped user may sign off.
 * SR-18 : Employee status Nonaktif blocks sign-off immediately.
 * SR-22 : Available = On Hand − Reserved.
 */
class InventoryService
{
    // ── Helpers ───────────────────────────────────────────────────────────

    private function nextGrNumber(): string
    {
        $last = DB::table('receipts')
            ->where('id', 'like', 'GR%')
            ->orderByDesc('id')
            ->value('id');
        $num = $last ? ((int) substr($last, 2)) + 1 : 1;
        return 'GR' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    private function nextDoNumber(): string
    {
        $last = DB::table('deliveries')
            ->where('id', 'like', 'DO%')
            ->orderByDesc('id')
            ->value('id');
        $num = $last ? ((int) substr($last, 2)) + 1 : 1;
        return 'DO' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }

    /**
     * SR-17/18: Validate employee can sign off.
     * Must be: Active status, Warehouse department (D-WH), has mapped user.
     */
    public function validateSignOff(string $employeeId): void
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            throw new \RuntimeException("SR-17: Employee {$employeeId} tidak ditemukan.");
        }
        if ($employee->status !== 'Aktif') {
            throw new \RuntimeException(
                "SR-18: Employee {$employee->name} ({$employeeId}) berstatus Nonaktif — sign-off diblokir."
            );
        }
        if ($employee->department_id !== 'D-WH') {
            throw new \RuntimeException(
                "SR-17: Employee {$employee->name} bukan dari departemen Warehouse."
            );
        }
        if (!$employee->mapped_user_id) {
            throw new \RuntimeException(
                "SR-17: Employee {$employee->name} tidak memiliki mapped user account."
            );
        }
    }

    // ── Delivery ──────────────────────────────────────────────────────────

    /**
     * Create a Delivery Order from a confirmed Sales Order.
     */
    public function createDelivery(string $salesOrderId, string $actorUser): Delivery
    {
        return DB::transaction(function () use ($salesOrderId, $actorUser) {
            $so = \App\Models\SalesOrder::with('lines')->findOrFail($salesOrderId);
            if (!in_array($so->status, ['confirmed', 'done'])) {
                throw new \RuntimeException("SO {$salesOrderId} harus Confirmed sebelum buat Delivery.");
            }

            $doId = $this->nextDoNumber();
            $delivery = Delivery::create([
                'id'              => $doId,
                'sales_order_id'  => $salesOrderId,
                'customer_id'     => $so->customer_id,
                'status'          => 'ready',
                'source_location' => 'WH-JKT',
                'created_by'      => $actorUser,
                'idempotency_key' => md5("delivery|{$salesOrderId}|" . time()),
            ]);

            foreach ($so->lines as $line) {
                $remaining = $line->quantity - $line->delivered_qty;
                if ($remaining > 0) {
                    DeliveryLine::create([
                        'delivery_id'         => $doId,
                        'sales_order_line_id' => (string) $line->id,
                        'product_id'          => $line->product_id,
                        'variant_sku'         => $line->variant_sku,
                        'product_name'        => $line->product_name,
                        'unit'                => $line->unit,
                        'demand_qty'          => $remaining,
                        'done_qty'            => 0,
                    ]);
                }
            }

            AuditLog::create([
                'document_type' => 'delivery',
                'document_id'   => $doId,
                'action'        => 'created',
                'actor_user'    => $actorUser,
                'description'   => "Delivery Order {$doId} dibuat dari SO {$salesOrderId}.",
                'acted_at'      => now(),
            ]);

            return $delivery->fresh(['lines']);
        });
    }

    /**
     * Validate/Done a Delivery Order.
     *
     * SR-04: no negative stock.
     * SR-07/SR-09: FEFO lot selection; expired lots blocked.
     * SR-16: idempotent — throws if already posted.
     * SR-17/18: active warehouse employee required.
     */
    public function processDelivery(
        string $deliveryId,
        string $signingEmployeeId,
        string $actorUser,
        array $doneQties = []
    ): Delivery {
        return DB::transaction(function () use ($deliveryId, $signingEmployeeId, $actorUser, $doneQties) {
            // SR-16: idempotency check
            $delivery = Delivery::with('lines')->findOrFail($deliveryId);
            if ($delivery->is_posted) {
                throw new \RuntimeException(
                    "SR-16: Delivery {$deliveryId} sudah diposting — tidak bisa diposting dua kali."
                );
            }
            if ($delivery->status === 'done') {
                throw new \RuntimeException("Delivery {$deliveryId} sudah Done.");
            }

            // SR-17/18: validate sign-off
            $this->validateSignOff($signingEmployeeId);
            $employee = Employee::find($signingEmployeeId);

            $idempotencyKey = md5("delivery_done|{$deliveryId}|{$actorUser}");

            foreach ($delivery->lines as $line) {
                $doneQty = isset($doneQties[$line->id]) ? (int) $doneQties[$line->id] : (int) $line->demand_qty;
                if ($doneQty <= 0) continue;

                $stock = OpeningStock::where('variant_sku', $line->variant_sku)->lockForUpdate()->first();
                if (!$stock) {
                    throw new \RuntimeException("SR-04: Stok {$line->variant_sku} tidak ditemukan.");
                }

                // SR-04 / SR-22: check available at source location
                $onHand    = $delivery->source_location === 'WH-JKT'
                    ? $stock->wh_jkt_on_hand
                    : $stock->hub_bdg_on_hand;
                $available = $onHand - $stock->reserved;
                if ($doneQty > $available) {
                    throw new \RuntimeException(
                        "SR-04: Stok tidak cukup untuk {$line->variant_sku}. "
                        . "Available: {$available}, diminta: {$doneQty}."
                    );
                }

                // SR-09/SR-07: FEFO lot picking for lot-tracked SKUs
                $lotId = null;
                $lots = ProductLot::fefoLotsFor($line->variant_sku, $delivery->source_location);
                if ($lots->count() > 0) {
                    $firstLot = $lots->first();
                    // SR-09: expired lots blocked
                    if ($firstLot->expiry_date->isPast()) {
                        throw new \RuntimeException(
                            "SR-09: Lot {$firstLot->lot_id} sudah kedaluwarsa ({$firstLot->expiry_date->toDateString()}) — dilarang dikirim."
                        );
                    }
                    $lotId = $firstLot->lot_id;
                    // Deduct from lot qty
                    $deductFromLot = min($doneQty, $firstLot->qty);
                    $firstLot->decrement('qty', $deductFromLot);
                }

                // Deduct stock at source location and company total
                $reserveRelease = min($stock->reserved, $doneQty);
                if ($delivery->source_location === 'WH-JKT') {
                    $stock->decrement('wh_jkt_on_hand', $doneQty);
                } else {
                    $stock->decrement('hub_bdg_on_hand', $doneQty);
                }
                $stock->decrement('company_total', $doneQty);
                if ($reserveRelease > 0) {
                    $stock->decrement('reserved', $reserveRelease);
                }
                $freshStock = $stock->fresh();
                $stock->update(['standard_value' => $freshStock->company_total * $freshStock->standard_cost]);

                // Backorder qty
                $backorder = max(0, $line->demand_qty - $doneQty);
                $line->update(['done_qty' => $doneQty, 'backorder_qty' => $backorder, 'lot_id' => $lotId]);

                // Update SO line delivered_qty (M2 owns this — confirmed answer 11)
                if ($line->sales_order_line_id) {
                    SalesOrderLine::where('id', $line->sales_order_line_id)
                        ->increment('delivered_qty', $doneQty);
                }

                // SR-03: perpetual COGS journal — Dr HPP 5100 / Cr Persediaan 1300
                $cogsAmount = $doneQty * $freshStock->standard_cost;
                $iKey = md5("cogs|{$deliveryId}|{$line->variant_sku}|{$doneQty}");
                if (!JournalEntry::where('idempotency_key', $iKey)->exists()) {
                    JournalEntry::create([
                        'invoice_id'      => null,
                        'journal_type'    => 'stock',
                        'reference'       => $deliveryId,
                        'idempotency_key' => $iKey,
                        'debit_account'   => '5100',
                        'credit_account'  => '1300',
                        'amount'          => $cogsAmount,
                        'description'     => "COGS Delivery {$deliveryId}: {$doneQty}×{$line->variant_sku} @ Rp{$freshStock->standard_cost}",
                        'posted_by'       => $actorUser,
                        'posted_at'       => now(),
                    ]);
                }

                // Perpetual stock move ledger
                StockMove::create([
                    'reference'       => $deliveryId,
                    'move_type'       => 'delivery',
                    'variant_sku'     => $line->variant_sku,
                    'from_location'   => $delivery->source_location,
                    'to_location'     => null,
                    'qty'             => $doneQty,
                    'unit_cost'       => $freshStock->standard_cost,
                    'total_value'     => $cogsAmount,
                    'lot_id'          => $lotId,
                    'actor_user'      => $actorUser,
                    'actor_employee'  => $signingEmployeeId,
                    'idempotency_key' => md5("sm_delivery|{$deliveryId}|{$line->variant_sku}"),
                ]);
            }

            $delivery->update([
                'status'               => 'done',
                'is_posted'            => true,
                'validator_employee_id'=> $signingEmployeeId,
                'validator_user'       => $actorUser,
                'validated_at'         => now(),
                'idempotency_key'      => $idempotencyKey,
            ]);

            AuditLog::create([
                'document_type'  => 'delivery',
                'document_id'    => $deliveryId,
                'action'         => 'validated',
                'actor_user'     => $actorUser,
                'actor_employee' => $signingEmployeeId,
                'description'    => "Delivery {$deliveryId} Done — divalidasi oleh {$employee->name} (SR-17).",
                'new_values'     => json_encode(['status' => 'done', 'validator' => $signingEmployeeId]),
                'acted_at'       => now(),
            ]);

            return $delivery->fresh(['lines']);
        });
    }

    // ── Receipt ───────────────────────────────────────────────────────────

    /**
     * Create a Goods Receipt from a confirmed PO.
     */
    public function createReceipt(string $poId, string $actorUser): Receipt
    {
        return DB::transaction(function () use ($poId, $actorUser) {
            $po = \App\Models\PurchaseOrder::with('lines')->findOrFail($poId);
            if ($po->status !== 'confirmed') {
                throw new \RuntimeException("PO {$poId} harus Confirmed sebelum buat Receipt.");
            }

            $grId = $this->nextGrNumber();
            $receipt = Receipt::create([
                'id'                   => $grId,
                'purchase_order_id'    => $poId,
                'vendor_id'            => $po->vendor_id,
                'status'               => 'draft',
                'destination_location' => 'WH-JKT',
                'created_by'           => $actorUser,
            ]);

            foreach ($po->lines as $line) {
                ReceiptLine::create([
                    'receipt_id'             => $grId,
                    'purchase_order_line_id' => (string) $line->id,
                    'product_id'             => $line->product_id,
                    'variant_sku'            => $line->variant_sku,
                    'product_name'           => $line->product_name,
                    'unit'                   => $line->unit,
                    'ordered_qty'            => $line->quantity,
                    'received_qty'           => 0,
                    'accepted_qty'           => 0,
                    'rejected_qty'           => 0,
                ]);
            }

            AuditLog::create([
                'document_type' => 'receipt',
                'document_id'   => $grId,
                'action'        => 'created',
                'actor_user'    => $actorUser,
                'description'   => "Goods Receipt {$grId} dibuat dari PO {$poId}.",
                'acted_at'      => now(),
            ]);

            return $receipt->fresh(['lines']);
        });
    }

    /**
     * Validate/Done a Goods Receipt.
     *
     * SR-06: only accepted_qty updates stock (not rejected_qty).
     * SR-16: idempotent.
     * SR-17/18: active warehouse employee.
     * GR journal: Dr 1300 / Cr 1350 on Done.
     */
    public function processReceipt(
        string $receiptId,
        string $signingEmployeeId,
        string $actorUser,
        array $lineData = []
    ): Receipt {
        return DB::transaction(function () use ($receiptId, $signingEmployeeId, $actorUser, $lineData) {
            // SR-16: idempotency
            $receipt = Receipt::with('lines')->findOrFail($receiptId);
            if ($receipt->is_posted) {
                throw new \RuntimeException(
                    "SR-16: Receipt {$receiptId} sudah diposting — idempotent guard."
                );
            }

            // SR-17/18
            $this->validateSignOff($signingEmployeeId);
            $employee = Employee::find($signingEmployeeId);

            $idempotencyKey = md5("receipt_done|{$receiptId}|{$actorUser}");

            foreach ($receipt->lines as $line) {
                $data        = $lineData[$line->id] ?? [];
                $receivedQty = (int)($data['received_qty'] ?? $line->ordered_qty);
                $acceptedQty = (int)($data['accepted_qty'] ?? $receivedQty);
                $rejectedQty = $receivedQty - $acceptedQty;

                $line->update([
                    'received_qty' => $receivedQty,
                    'accepted_qty' => $acceptedQty,
                    'rejected_qty' => $rejectedQty,
                ]);

                if ($acceptedQty <= 0) continue;

                $stock = OpeningStock::where('variant_sku', $line->variant_sku)->lockForUpdate()->first();
                if (!$stock) {
                    throw new \RuntimeException("Stock row tidak ditemukan untuk {$line->variant_sku}.");
                }

                // SR-06: Add only accepted_qty to destination location
                if ($receipt->destination_location === 'WH-JKT') {
                    $stock->increment('wh_jkt_on_hand', $acceptedQty);
                } else {
                    $stock->increment('hub_bdg_on_hand', $acceptedQty);
                }
                $stock->increment('company_total', $acceptedQty);
                $freshStock    = $stock->fresh();
                $valueIncrease = $acceptedQty * $freshStock->standard_cost;
                $stock->update(['standard_value' => $freshStock->company_total * $freshStock->standard_cost]);

                // Update PO line accepted/received qty
                if ($line->purchase_order_line_id) {
                    PurchaseOrderLine::where('id', $line->purchase_order_line_id)->update([
                        'received_qty' => DB::raw("received_qty + {$receivedQty}"),
                        'accepted_qty' => DB::raw("accepted_qty + {$acceptedQty}"),
                        'rejected_qty' => DB::raw("rejected_qty + {$rejectedQty}"),
                    ]);
                }

                // Lot creation for lot-tracked SKUs (SR-09)
                if ($line->lot_id && $line->expiry_date) {
                    \App\Models\ProductLot::updateOrCreate(
                        ['lot_id' => $line->lot_id],
                        [
                            'variant_sku'      => $line->variant_sku,
                            'location'         => $receipt->destination_location,
                            'qty'              => $acceptedQty,
                            'expiry_date'      => $line->expiry_date,
                            'selection_policy' => 'FEFO',
                        ]
                    );
                }

                // GR journal: Dr 1300 Persediaan / Cr 1350 Stock Interim (confirmed answer 9)
                $iKey = md5("receipt_stock|{$receiptId}|{$line->variant_sku}|{$acceptedQty}");
                if (!JournalEntry::where('idempotency_key', $iKey)->exists()) {
                    JournalEntry::create([
                        'invoice_id'      => null,
                        'journal_type'    => 'stock',
                        'reference'       => $receiptId,
                        'idempotency_key' => $iKey,
                        'debit_account'   => '1300',
                        'credit_account'  => '1350',
                        'amount'          => $valueIncrease,
                        'description'     => "GR {$receiptId}: +{$acceptedQty}×{$line->variant_sku} Rp{$freshStock->standard_cost}/unit Dr Persediaan/Cr Stock Interim",
                        'posted_by'       => $actorUser,
                        'posted_at'       => now(),
                    ]);
                }

                // Perpetual stock move ledger
                StockMove::create([
                    'reference'       => $receiptId,
                    'move_type'       => 'receipt',
                    'variant_sku'     => $line->variant_sku,
                    'from_location'   => null,
                    'to_location'     => $receipt->destination_location,
                    'qty'             => $acceptedQty,
                    'unit_cost'       => $freshStock->standard_cost,
                    'total_value'     => $valueIncrease,
                    'lot_id'          => $line->lot_id,
                    'actor_user'      => $actorUser,
                    'actor_employee'  => $signingEmployeeId,
                    'idempotency_key' => md5("sm_receipt|{$receiptId}|{$line->variant_sku}"),
                ]);
            }

            $receipt->update([
                'status'               => 'done',
                'is_posted'            => true,
                'validator_employee_id'=> $signingEmployeeId,
                'validator_user'       => $actorUser,
                'validated_at'         => now(),
                'idempotency_key'      => $idempotencyKey,
            ]);

            AuditLog::create([
                'document_type'  => 'receipt',
                'document_id'    => $receiptId,
                'action'         => 'validated',
                'actor_user'     => $actorUser,
                'actor_employee' => $signingEmployeeId,
                'description'    => "GR {$receiptId} Done — divalidasi oleh {$employee->name} (SR-17).",
                'new_values'     => json_encode(['status' => 'done', 'validator' => $signingEmployeeId]),
                'acted_at'       => now(),
            ]);

            return $receipt->fresh(['lines']);
        });
    }

    // ── Internal Transfer ─────────────────────────────────────────────────

    /**
     * SR-14: Transfer stock between WH-JKT ↔ HUB-BDG.
     * Company total unchanged. No AR/AP/tax journal.
     * SR-22: Available at source must be sufficient (On Hand - Reserved).
     */
    public function transferStock(
        string $fromLocation,
        string $toLocation,
        string $variantSku,
        int $qty,
        string $actorUser
    ): void {
        DB::transaction(function () use ($fromLocation, $toLocation, $variantSku, $qty, $actorUser) {
            $stock = OpeningStock::where('variant_sku', $variantSku)->lockForUpdate()->first();
            if (!$stock) {
                throw new \RuntimeException("Stok {$variantSku} tidak ditemukan.");
            }

            // SR-22: check source available (On Hand at location - Reserved)
            $sourceOnHand = $fromLocation === 'WH-JKT'
                ? $stock->wh_jkt_on_hand
                : $stock->hub_bdg_on_hand;
            // Reserved is tracked at company level; apply conservatively to source
            $available = $fromLocation === 'WH-JKT'
                ? ($stock->wh_jkt_on_hand - $stock->reserved)
                : $stock->hub_bdg_on_hand;

            if ($qty > $available) {
                throw new \RuntimeException(
                    "SR-22: Stok available di {$fromLocation} tidak cukup. "
                    . "Available: {$available}, diminta: {$qty}."
                );
            }

            // SR-14: Decrement source, increment destination. company_total unchanged.
            if ($fromLocation === 'WH-JKT') {
                $stock->decrement('wh_jkt_on_hand', $qty);
                $stock->increment('hub_bdg_on_hand', $qty);
            } else {
                $stock->decrement('hub_bdg_on_hand', $qty);
                $stock->increment('wh_jkt_on_hand', $qty);
            }
            // standard_value unchanged (company_total × standard_cost stays the same)

            $transferRef = 'TRF-' . strtoupper(substr($variantSku, 0, 6)) . '-' . date('Ymd-His');
            $unitCost    = $stock->standard_cost;

            StockMove::create([
                'reference'       => $transferRef,
                'move_type'       => 'transfer',
                'variant_sku'     => $variantSku,
                'from_location'   => $fromLocation,
                'to_location'     => $toLocation,
                'qty'             => $qty,
                'unit_cost'       => $unitCost,
                'total_value'     => $qty * $unitCost,
                'lot_id'          => null,
                'actor_user'      => $actorUser,
                'actor_employee'  => null,
                'idempotency_key' => md5("transfer|{$variantSku}|{$fromLocation}|{$toLocation}|{$qty}|" . microtime()),
            ]);

            AuditLog::create([
                'document_type' => 'stock_transfer',
                'document_id'   => $transferRef,
                'action'        => 'transfer',
                'actor_user'    => $actorUser,
                'description'   => "Transfer {$qty}×{$variantSku} dari {$fromLocation} ke {$toLocation} (SR-14).",
                'old_values'    => json_encode(['from' => $fromLocation, 'qty_before' => $sourceOnHand]),
                'new_values'    => json_encode(['to' => $toLocation, 'qty_transferred' => $qty]),
                'acted_at'      => now(),
            ]);
        });
    }

    // ── FEFO Lot Allocation ───────────────────────────────────────────────

    /**
     * SR-07: Allocate qty from FEFO-sorted lots.
     * Returns array of ['lot_id' => ..., 'qty_allocated' => ...].
     * Throws if insufficient lot stock.
     */
    public function allocateFefo(string $variantSku, int $requiredQty, ?string $locationCode = null): array
    {
        $lots      = ProductLot::fefoLotsFor($variantSku, $locationCode);
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

    // ── COGS Journal (direct call) ────────────────────────────────────────

    /**
     * SR-03: Post perpetual COGS journal entry.
     * Dr COGS (5100) / Cr Inventory (1300) @ standard cost × qty.
     */
    public function postCogsJournal(string $reference, string $variantSku, int $qty, string $postedBy): JournalEntry
    {
        $stock = OpeningStock::where('variant_sku', $variantSku)->firstOrFail();
        $amount = $qty * $stock->standard_cost;
        $iKey   = md5("cogs_direct|{$reference}|{$variantSku}|{$qty}");

        return JournalEntry::firstOrCreate(
            ['idempotency_key' => $iKey],
            [
                'invoice_id'      => null,
                'journal_type'    => 'stock',
                'reference'       => $reference,
                'debit_account'   => '5100',
                'credit_account'  => '1300',
                'amount'          => $amount,
                'description'     => "COGS {$reference}: {$qty}×{$variantSku} @ Rp{$stock->standard_cost}",
                'posted_by'       => $postedBy,
                'posted_at'       => now(),
            ]
        );
    }
}
