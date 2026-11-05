<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\Lead;
use App\Services\SalesCrmService;
use Illuminate\Http\Request;

/**
 * LeadController — CRM Lead & Opportunity (M01)
 *
 * Features : F-04 (Lead pipeline), F-21 (Audit log)
 * Screens  : S-04 (CRM Kanban / Form)
 * Tests    : T-07 (convert Lead → Opportunity), T-08 (SR-19 relation lock)
 * Roles    : Sales, Owner
 */
class LeadController extends Controller
{
    public function __construct(private SalesCrmService $crm) {}

    /**
     * S-04: CRM Pipeline — List / Kanban view.
     * T-07: Lead L00003 visible in pipeline.
     */
    public function index(Request $request)
    {
        $stage  = $request->input('stage');
        $search = $request->input('search');

        $query = Lead::orderBy('id', 'desc');

        if ($stage) {
            $query->where('stage', $stage);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('partner_name', 'like', "%{$search}%");
            });
        }

        $leads = $query->get();

        $stages = [
            'new'       => $leads->where('stage', 'new')->values(),
            'qualified' => $leads->where('stage', 'qualified')->values(),
            'won'       => $leads->where('stage', 'won')->values(),
            'lost'      => $leads->where('stage', 'lost')->values(),
        ];

        return view('sales.leads.index', [
            'leads'      => $leads,
            'stages'     => $stages,
            'customers'  => Customer::orderBy('name')->get(),
            'filters'    => ['stage' => $stage, 'search' => $search],
        ]);
    }

    /**
     * Show a single lead / opportunity form.
     */
    public function show(string $id)
    {
        $lead        = Lead::findOrFail($id);
        $auditTrail  = AuditLog::forDocument('lead', $id);
        $relatedDocs = AuditLog::relatedDocuments('lead', $id);

        return view('sales.leads.show', [
            'lead'        => $lead,
            'auditTrail'  => $auditTrail,
            'relatedDocs' => $relatedDocs,
        ]);
    }

    /**
     * Store a new lead (draft form).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'partner_id'       => 'nullable|exists:contacts_customers,id',
            'expected_revenue' => 'nullable|integer|min:0',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $partner = isset($validated['partner_id'])
            ? Customer::find($validated['partner_id'])
            : null;

        $lead = $this->crm->createLead([
            'partner_id'       => $partner?->id,
            'partner_name'     => $partner?->name,
            'expected_revenue' => (int) ($validated['expected_revenue'] ?? 0),
            'notes'            => $validated['notes'] ?? null,
            'salesperson_id'   => auth()->user()->username,
            'salesperson_name' => auth()->user()->name,
            'created_by'       => auth()->user()->username,
        ]);

        return redirect()
            ->route('crm.leads.show', $lead->id)
            ->with('success', "Lead {$lead->id} berhasil dibuat.");
    }

    /**
     * Convert Lead → Opportunity (stage: new → qualified).
     * T-07 normal flow / T-08 SR-19 rule test.
     */
    public function convertToOpportunity(Request $request, string $id)
    {
        try {
            $lead = $this->crm->convertToOpportunity($id, auth()->user()->username);

            return redirect()
                ->route('crm.leads.show', $lead->id)
                ->with('success', "Lead {$id} berhasil dikonversi ke Opportunity (Qualified).");
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('crm.leads.show', $id)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Update a lead's customer relation.
     * SR-19: blocked if lead is already an Opportunity.
     */
    public function updateCustomer(Request $request, string $id)
    {
        $partnerId = $request->input('partner_id');

        // Deleting customer relation from Opportunity → SR-19 block
        if (empty($partnerId)) {
            try {
                $this->crm->removeCustomerRelation($id, auth()->user()->username);
                return back()->with('success', 'Relasi pelanggan dihapus.');
            } catch (\RuntimeException $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        $partner = Customer::findOrFail($partnerId);
        Lead::findOrFail($id)->update([
            'partner_id'   => $partner->id,
            'partner_name' => $partner->name,
        ]);

        return back()->with('success', 'Relasi pelanggan diperbarui.');
    }
}
