<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Receipt;
use App\Models\PurchaseOrder;
use App\Models\Employee;
use App\Services\InventoryService;
use Illuminate\Http\Request;

/**
 * ReceiptController — Goods Receipt & Inspection (M02)
 *
 * Features: F-08 (Goods Receipt), F-12 (Lot tracking on receipt)
 * Screens:  S-09 sub-view (Receipt Form)
 * Tests:    T-06 (stock interim journal), T-11 (anti-negative SR-04), T-16, T-18 (sign-off SR-17/18)
 * Roles:    Warehouse (validate/sign-off), Purchase (create), Owner
 */
class ReceiptController extends Controller
{
    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        $receipts = Receipt::with(['purchaseOrder'])->orderByDesc('created_at')->paginate(20);
        return view('inventory.receipts.index', compact('receipts'));
    }

    public function store(Request $request)
    {
        $request->validate(['purchase_order_id' => 'required|exists:purchase_orders,id']);
        try {
            $receipt = $this->inventoryService->createReceipt(
                $request->purchase_order_id,
                auth()->user()->username ?? auth()->user()->name
            );
            return redirect()->route('inventory.receipts.show', $receipt->id)
                ->with('success', "Receipt {$receipt->id} dibuat dari PO {$request->purchase_order_id}.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $receipt   = Receipt::with(['purchaseOrder', 'lines.product', 'validatorEmployee'])->findOrFail($id);
        $employees = Employee::where('department_id', 'D-WH')->where('status', 'Aktif')->get();
        return view('inventory.receipts.show', compact('receipt', 'employees'));
    }

    public function validateReceipt(Request $request, string $id)
    {
        $request->validate(['validator_employee_id' => 'required|exists:employees,id']);

        $lineData = [];
        if ($request->has('lines')) {
            foreach ($request->lines as $lineId => $data) {
                $lineData[(int) $lineId] = [
                    'received_qty' => (int)($data['received_qty'] ?? 0),
                    'accepted_qty' => (int)($data['accepted_qty'] ?? 0),
                ];
            }
        }

        try {
            $this->inventoryService->processReceipt(
                $id,
                $request->validator_employee_id,
                auth()->user()->username ?? auth()->user()->name,
                $lineData
            );
            return redirect()->route('inventory.receipts.show', $id)
                ->with('success', "Receipt {$id} Done — stok bertambah (accepted qty only, SR-06).");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
