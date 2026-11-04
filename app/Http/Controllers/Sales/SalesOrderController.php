<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Services\SalesCrmService;
use Illuminate\Http\Request;

/**
 * SalesOrderController — Quotation → Sales Order lifecycle (M01)
 *
 * Features : F-05 (Quotation → SO), F-06 (Discount approval), F-10 (Credit limit),
 *            F-21 (Audit log), F-22 (Unique SO number)
 * Screens  : S-05 (Quotation / SO form)
 * Tests    : T-03, T-04 (credit), T-09 (SO S00008), T-10 (discount SR-07/08)
 * Roles    : confirm → Sales | approve_discount → Finance | view → Sales, Owner
 */
class SalesOrderController extends Controller
{
    public function __construct(private SalesCrmService $crm) {}

    /**
     * S-05 list view: filter by status, customer, search.
     */
    public function index(Request $request)
    {
        $query = SalesOrder::with('customer')
            ->orderByDesc('id');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($customerId = $request->input('customer_id')) {
            $query->where('customer_id', $customerId);
        }
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $orders = $query->paginate(20);

        return view('sales.orders.index', [
            'orders'    => $orders,
            'customers' => Customer::orderBy('name')->get(),
            'filters'   => $request->only('status', 'customer_id', 'search'),
        ]);
    }

    /**
     * Blank quotation form — load customer list + product catalogue.
     */
    public function create()
    {
        return view('sales.orders.create', [
            'customers' => Customer::orderBy('name')->get(),
            'products'  => Product::orderBy('name')->get(),
        ]);
    }

    /**
     * Save a draft Quotation.
     * T-09: creates S00008 from seed baseline.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id'          => 'required|exists:contacts_customers,id',
            'lines'                => 'required|array|min:1',
            'lines.*.variant_sku'  => 'required|string',
            'lines.*.quantity'     => 'required|integer|min:1',
            'lines.*.unit_price'   => 'required|integer|min:0',
            'lines.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ]);

        $productMap = Product::whereIn('variant_sku', collect($request->lines)->pluck('variant_sku'))
            ->get()->keyBy('variant_sku');

        $lines = [];
        foreach ($request->lines as $l) {
            $product = $productMap[$l['variant_sku']] ?? null;
            if (!$product) continue;

            $lines[] = [
                'product_id'       => $product->id,
                'variant_sku'      => $product->variant_sku,
                'product_name'     => $product->name,
                'unit'             => $product->unit,
                'quantity'         => (int) $l['quantity'],
                'unit_price'       => (int) $l['unit_price'],
                'discount_percent' => (float) ($l['discount_percent'] ?? 0.00),
                'source_location'  => $l['source_location'] ?? 'WH-JKT',
            ];
        }

        try {
            $so = $this->crm->createQuotation([
                'customer_id'   => $request->customer_id,
                'payment_terms' => $request->payment_terms,
                'lead_id'       => $request->lead_id ?? null,
                'created_by'    => auth()->user()->username,
                'lines'         => $lines,
            ]);

            return redirect()
                ->route('sales.orders.show', $so->id)
                ->with('success', "Quotation {$so->id} berhasil dibuat.");
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Form view — lines, status bar, smart buttons.
     */
    public function show(string $id)
    {
        $so          = SalesOrder::with(['lines', 'customer'])->findOrFail($id);
        $auditTrail  = AuditLog::forDocument('sales_order', $id);
        $relatedDocs = AuditLog::relatedDocuments('sales_order', $id);

        // T-03: credit exposure calculation
        $exposure = $this->crm->creditExposure($so->customer_id);

        return view('sales.orders.show', [
            'so'           => $so,
            'auditTrail'   => $auditTrail,
            'relatedDocs'  => $relatedDocs,
            'exposure'     => $exposure,
            'creditLimit'  => $so->customer->credit_limit,
        ]);
    }

    /**
     * Confirm Quotation → Sales Order.
     *
     * SR enforcement chain: discount gate (SR-07/08) → credit check (SR-11)
     * → reserve stock (SR-22) → lock lines (SR-12) → delivery promise (SR-03)
     *
     * T-09: S00008 confirmed, net Rp250.000, PPN Rp27.500, gross Rp277.500
     * T-10: diskon 15% → blocked, status → to_approve
     * T-04: 40 P01 for C01 → blocked (exposure Rp2.232.000 > limit Rp2.000.000)
     */
    public function confirm(Request $request, string $id)
    {
        try {
            $so = $this->crm->confirmSalesOrder($id, auth()->user()->username);

            $msg = $so->isToApprove()
                ? "SO {$id}: diskon >10% ditemukan — menunggu persetujuan Finance (SR-08)."
                : "Sales Order {$id} berhasil dikonfirmasi.";

            return redirect()
                ->route('sales.orders.show', $id)
                ->with($so->isToApprove() ? 'warning' : 'success', $msg);

        } catch (\RuntimeException $e) {
            return redirect()
                ->route('sales.orders.show', $id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Finance approves discount on a to_approve SO.
     * SR-08: Finance/Owner only.
     * T-10: post-approval SO is confirmed with locked lines.
     */
    public function approveDiscount(Request $request, string $id)
    {
        try {
            $so = $this->crm->approveDiscount($id, auth()->user()->username);
            return redirect()
                ->route('sales.orders.show', $id)
                ->with('success', "Diskon pada SO {$id} disetujui. SO sekarang Confirmed.");
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('sales.orders.show', $id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Finance rejects discount request → SO back to draft.
     */
    public function rejectDiscount(Request $request, string $id)
    {
        try {
            $this->crm->rejectDiscount($id, auth()->user()->username);
            return redirect()
                ->route('sales.orders.show', $id)
                ->with('warning', "Diskon pada SO {$id} ditolak. SO kembali ke Draft.");
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('sales.orders.show', $id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a Sales Order and release stock reservations.
     */
    public function cancel(Request $request, string $id)
    {
        try {
            $this->crm->cancelSalesOrder(
                $id,
                auth()->user()->username,
                $request->input('reason', '')
            );
            return redirect()
                ->route('sales.orders.index')
                ->with('warning', "Sales Order {$id} dibatalkan. Reservasi stok dilepaskan.");
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('sales.orders.show', $id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * API: credit exposure for a customer (used by JS on create form).
     */
    public function creditExposure(string $customerId)
    {
        try {
            $customer = Customer::findOrFail($customerId);
            $exposure = $this->crm->creditExposure($customerId);
            return response()->json([
                'customer_id'    => $customerId,
                'customer_name'  => $customer->name,
                'credit_limit'   => $customer->credit_limit,
                'open_ar'        => $customer->open_ar,
                'exposure'       => $exposure,
                'available'      => max(0, $customer->credit_limit - $exposure),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
