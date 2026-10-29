<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AuditLog;

class AuditController extends Controller
{
    /**
     * S-18 · F-21 · Audit / Related Documents
     * Menampilkan daftar audit log dengan filter: document, actor, action, tanggal.
     * Mendukung T-35 (lacak PO00007 → related docs) dan T-36 (idempotent re-validate proof).
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'document_ref',
            'document_type',
            'actor',
            'action',
            'date_from',
            'date_to',
        ]);

        // Strip empty strings
        $filters = array_filter($filters, fn($v) => $v !== '' && $v !== null);

        $query = AuditLog::filteredQuery($filters);

        // Pagination — 50 baris per halaman
        $logs = $query->paginate(50)->withQueryString();

        // Data untuk filter dropdown
        $distinctActions = AuditLog::distinctActions();
        $distinctTypes   = AuditLog::distinctTypes();

        // Total count (untuk S-18 KPI summary)
        $totalCount    = AuditLog::count();
        $filteredCount = $query->count(); // sebelum paginate

        return view('reporting.audit', compact(
            'logs',
            'filters',
            'distinctActions',
            'distinctTypes',
            'totalCount',
            'filteredCount'
        ));
    }

    /**
     * S-18 / F-21: Tampilkan detail audit untuk satu dokumen tertentu.
     * URL: /reporting/audit/{type}/{id}
     * Digunakan sebagai "drill-down" dari smart-button Related Docs.
     */
    public function show(Request $request, string $docType, string $docId)
    {
        $logs    = AuditLog::forDocument($docType, $docId);
        $related = AuditLog::relatedDocuments($docType, $docId);

        return view('reporting.audit-show', compact('logs', 'related', 'docType', 'docId'));
    }
}
