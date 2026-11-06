<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\SalesOrder;
use App\Models\Employee;
use App\Models\OpeningStock;
use App\Services\InventoryService;
use Illuminate\Http\Request;

/**
 * DeliveryController — Delivery Order & FEFO Picking (M02)
 *
 * Features: F-11 (Delivery), F-12 (FEFO lot picking SR-07), F-17 (no negative stock SR-04)
 * Screens:  S-06 sub-view (Delivery Form)
 * Tests:    T-11 (anti-negative), T-12 (FEFO LOT-TEA-01 first), T-13 (stock transfer), T-17, T-18
 * Roles:    Warehouse, Owner
 */
class DeliveryController extends Controller
{
    private InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index()
    {
        $deliveries = Delivery::with(['salesOrder'])->orderByDesc('created_at')->paginate(20);
        return view('inventory.deliveries.index', compact('deliveries'));
    }

    public function create(Request $request)
    {
        $soId         = $request->get('so_id');
        $so           = $soId ? SalesOrder::with('lines.product')->findOrFail($soId) : null;
        $confirmedSOs = SalesOrder::where('status', 'confirmed')->orderByDesc('created_at')->get();
        $employees    = Employee::where('department_id', 'D-WH')->where('status', 'Aktif')->get();
        return view('inventory.deliveries.create', compact('so', 'confirmedSOs', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate(['sales_order_id' => 'required|exists:sales_orders,id']);
        try {
            $delivery = $this->inventoryService->createDelivery(
                $request->sales_order_id,
                auth()->user()->username ?? auth()->user()->name
            );
            return redirect()->route('inventory.deliveries.show', $delivery->id)
                ->with('success', "Delivery Order {$delivery->id} dibuat.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $delivery  = Delivery::with(['salesOrder', 'lines.product', 'validatorEmployee'])->findOrFail($id);
        $employees = Employee::where('department_id', 'D-WH')->where('status', 'Aktif')->get();
        $stockInfo = [];
        foreach ($delivery->lines as $line) {
            $stock = OpeningStock::where('variant_sku', $line->variant_sku)->first();
            if ($stock) {
                $stockInfo[$line->variant_sku] = [
                    'on_hand'   => $stock->wh_jkt_on_hand,
                    'reserved'  => $stock->reserved,
                    'available' => $stock->wh_jkt_on_hand - $stock->reserved,
                ];
            }
        }
        return view('inventory.deliveries.show', compact('delivery', 'employees', 'stockInfo'));
    }

    public function validateDelivery(Request $request, string $id)
    {
        $request->validate([
            'validator_employee_id' => 'required|exists:employees,id',
        ]);

        // Build done_qties from request
        $doneQties = [];
        if ($request->has('done_qties')) {
            foreach ($request->done_qties as $lineId => $qty) {
                $doneQties[(int) $lineId] = (int) $qty;
            }
        }

        try {
            $this->inventoryService->processDelivery(
                $id,
                $request->validator_employee_id,
                auth()->user()->username ?? auth()->user()->name,
                $doneQties
            );
            return redirect()->route('inventory.deliveries.show', $id)
                ->with('success', "Delivery {$id} berhasil divalidasi — stok dikurangi, COGS diposting.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
