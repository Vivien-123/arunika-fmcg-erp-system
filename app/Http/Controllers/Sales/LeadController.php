<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * LeadController — CRM Lead & Opportunity (M01)
 *
 * Features : F-04 (Lead pipeline), F-02 (Customer relation)
 * Screens  : S-03 (CRM Kanban), S-04 (Lead Form)
 * Tests    : T-07 (convert Lead → Opportunity), T-08 (SR-19 relation lock)
 * Roles    : Sales, Owner
 *
 * TODO M01: Implement index(), show(), store(), convertToOpportunity()
 */
class LeadController extends Controller
{
    public function index(Request $request)
    {
        // TODO: List / Kanban of leads with stage filter
    }

    public function show(string $id)
    {
        // TODO: Lead form view with smart buttons
    }

    public function store(Request $request)
    {
        // TODO: Create new lead — call SalesCrmService::createLead()
    }

    public function convertToOpportunity(Request $request, string $id)
    {
        // TODO: SR-19 — partner relation must be set before conversion
        //       AuditLog::record('lead', $id, 'converted', ...) after success
    }
}
