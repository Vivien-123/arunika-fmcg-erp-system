<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderLine;
use App\Models\Vendor;
use App\Models\VendorPricelist;
use App\Models\Product;
use App\Models\Receipt;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

/**
 * PurchaseOrderController — RFQ → Purchase Order lifecycle (M02)
 *
 * Features: F-11 (RFQ to PO), F-08 (Goods Receipt)
 * Screens:  S-08 (PO List), S-09 (PO Form)
 * Tests:    T-05 (PO00008 creation), T-15 (PO Confirm & Price Lock SR-21), T-16
 * Roles:    Purchase, Owner, Finance (read)
 */
class PurchaseOrderController extends Controller
{
    private PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function index(Request $request)
    {
        $status = $request->get('status', '');
        $query  = PurchaseOrder::with('vendor')->orderByDesc('created_at');
        if ($status) {
            $query->where('status', $status);
        }
        $orders = $query->paginate(20);
        return view('purchase.orders.index', compact('orders', 'status'));
    }

    public function create()
    {
        $vendors   = Vendor::orderBy('name')->get();
        $products  = Product::orderBy('name')->get();
        $pricelist = VendorPricelist::all()->groupBy('vendor_id');
        return view('purchase.orders.create', compact('vendors', 'products', 'pricelist'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id'           => 'required|exists:contacts_vendors,id',
            'lines'               => 'required|array|min:1',
            'lines.*.variant_sku' => 'required|exists:products,variant_sku',
            'lines.*.quantity'    => 'required|integer|min:1',
        ]);

        try {
            $po = $this->purchaseService->createRfq([
                'vendor_id'  => $request->vendor_id,
                'lines'      => $request->lines,
                'created_by' => auth()->user()->username ?? auth()->user()->name,
            ]);

            return redirect()->route('purchase.orders.show', $po->id)
                ->with('success', "RFQ {$po->id} berhasil dibuat.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $order        = PurchaseOrder::with(['vendor', 'lines.product'])->findOrFail($id);
        $receipts     = Receipt::where('purchase_order_id', $id)->with('lines')->get();
        $receiptCount = $receipts->count();
        $acceptedTotal = $receipts->where('status', 'done')
            ->flatMap(fn($r) => $r->lines)->sum('accepted_qty');
        return view('purchase.orders.show', compact('order', 'receipts', 'receiptCount', 'acceptedTotal'));
    }

    public function confirm(Request $request, string $id)
    {
        try {
            $this->purchaseService->confirmPurchaseOrder(
                $id,
                auth()->user()->username ?? auth()->user()->name
            );
            return redirect()->route('purchase.orders.show', $id)
                ->with('success', "PO {$id} dikonfirmasi — harga terkunci (SR-21).");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, string $id)
    {
        $po = PurchaseOrder::findOrFail($id);
        if ($po->status === 'done') {
            return redirect()->back()->with('error', 'PO sudah Done — tidak bisa dibatalkan.');
        }
        if ($po->status === 'confirmed') {
            return redirect()->back()->with('error', 'PO sudah Confirmed — tidak bisa dibatalkan langsung. Hubungi supervisor.');
        }
        $po->update(['status' => 'cancelled']);
        AuditLog::create([
            'document_type' => 'purchase_order',
            'document_id'   => $id,
            'action'        => 'cancelled',
            'actor_user'    => auth()->user()->username ?? auth()->user()->name,
            'description'   => "PO {$id} dibatalkan.",
            'acted_at'      => now(),
        ]);
        return redirect()->route('purchase.orders.index')
            ->with('success', "PO {$id} dibatalkan.");
    }

    public function createReceipt(string $id)
    {
        $order = PurchaseOrder::with(['vendor', 'lines.product'])->findOrFail($id);
        if ($order->status !== 'confirmed') {
            return redirect()->back()->with('error', 'PO harus Confirmed sebelum buat Receipt.');
        }
        $employees = Employee::where('department_id', 'D-WH')
            ->where('status', 'Aktif')->get();
        return view('purchase.receipts.create', compact('order', 'employees'));
    }
}
