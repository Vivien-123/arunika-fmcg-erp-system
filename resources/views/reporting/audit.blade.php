@extends('layouts.app')
@section('title','Audit / Related Documents')
@php
    $currentApp  = 'Reporting';
    $sidebarMenu = [
        ['label' => 'Audit Log',   'url' => route('reporting.audit'),  'icon' => '📋'],
        ['label' => 'Dashboard',   'url' => route('home'),             'icon' => '📈'],
    ];

    /* ── warna per action ── */
    $actionColors = [
        'created'                => 'bg-gray-100 text-gray-600',
        'login'                  => 'bg-blue-100 text-blue-700',
        'logout'                 => 'bg-gray-100 text-gray-500',
        'status_changed'         => 'bg-yellow-100 text-yellow-700',
        'sign_off_authorized'    => 'bg-green-100 text-green-700',
        'sign_off_rejected'      => 'bg-red-100 text-red-700',
        'access_denied'          => 'bg-red-100 text-red-700',
        'private_access_denied'  => 'bg-orange-100 text-orange-700',
        'confirm'                => 'bg-blue-100 text-blue-700',
        'validate'               => 'bg-green-100 text-green-700',
        'post'                   => 'bg-purple-100 text-purple-700',
        'payment'                => 'bg-emerald-100 text-emerald-700',
        'return'                 => 'bg-rose-100 text-rose-700',
        'rejected_edit'          => 'bg-red-100 text-red-600',
        'double_post_blocked'    => 'bg-red-100 text-red-700',
    ];
@endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-500">Reporting</span>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700 font-medium">Audit / Related Documents</span>
@endsection

@section('view_switcher')
    <span class="px-2 py-1 text-xs bg-[#714B67] text-white border-r">☰ List</span>
@endsection

@section('control_panel_extra')
{{-- ① Search · Filters · Group By — S-18 --}}
<form method="GET" action="{{ route('reporting.audit') }}" class="flex items-center gap-2 flex-wrap text-xs">
    {{-- Document reference --}}
    <input type="text" name="document_ref"
           value="{{ $filters['document_ref'] ?? '' }}"
           placeholder="Cari dokumen (S00007…)"
           class="border border-gray-300 rounded px-2 py-1 text-xs w-36">

    {{-- Document type --}}
    <select name="document_type" class="border border-gray-300 rounded px-2 py-1 text-xs text-gray-600">
        <option value="">Semua Tipe</option>
        @foreach($distinctTypes as $dt)
            <option value="{{ $dt }}" {{ ($filters['document_type'] ?? '') === $dt ? 'selected' : '' }}>
                {{ $dt }}
            </option>
        @endforeach
    </select>

    {{-- Actor --}}
    <input type="text" name="actor"
           value="{{ $filters['actor'] ?? '' }}"
           placeholder="Actor / Employee ID"
           class="border border-gray-300 rounded px-2 py-1 text-xs w-36">

    {{-- Action --}}
    <select name="action" class="border border-gray-300 rounded px-2 py-1 text-xs text-gray-600">
        <option value="">Semua Aksi</option>
        @foreach($distinctActions as $act)
            <option value="{{ $act }}" {{ ($filters['action'] ?? '') === $act ? 'selected' : '' }}>
                {{ $act }}
            </option>
        @endforeach
    </select>

    {{-- Date range --}}
    <input type="date" name="date_from"
           value="{{ $filters['date_from'] ?? '' }}"
           class="border border-gray-300 rounded px-2 py-1 text-xs">
    <input type="date" name="date_to"
           value="{{ $filters['date_to'] ?? '' }}"
           class="border border-gray-300 rounded px-2 py-1 text-xs">

    <button type="submit"
            class="bg-[#714B67] text-white rounded px-3 py-1 text-xs hover:bg-[#5c3d56] transition">
        🔍 Filter
    </button>
    @if(count($filters))
    <a href="{{ route('reporting.audit') }}"
       class="border border-gray-300 bg-gray-50 rounded px-3 py-1 text-xs text-gray-600 hover:bg-gray-100 transition">
        ✕ Reset
    </a>
    @endif
</form>
@endsection

@section('content')
<div class="max-w-7xl mx-auto space-y-4">

    {{-- ③ Fields / KPI summary — S-18 ③ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Total Log</p>
            <p class="text-2xl font-bold text-gray-800">{{ number_format($totalCount) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Hasil Filter</p>
            <p class="text-2xl font-bold text-[#714B67]">{{ number_format($filteredCount) }}</p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Dokumen Unik</p>
            <p class="text-2xl font-bold text-gray-800">
                {{ \App\Models\AuditLog::query()->distinct()->count('document_id') }}
            </p>
        </div>
        <div class="bg-white border border-gray-200 rounded-xl px-4 py-3 shadow-sm">
            <p class="text-xs text-gray-400 uppercase tracking-wide mb-0.5">Aksi Unik</p>
            <p class="text-2xl font-bold text-gray-800">{{ count($distinctActions) }}</p>
        </div>
    </div>

    {{-- Active filters badge --}}
    @if(count($filters))
    <div class="flex items-center gap-2 flex-wrap text-xs">
        <span class="text-gray-500 font-medium">Filter aktif:</span>
        @foreach($filters as $key => $val)
        <span class="bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-mono">
            {{ $key }}: {{ $val }}
        </span>
        @endforeach
    </div>
    @endif

    {{-- ④ Detail / List — Reference, Action, Actor, Result — S-18 ④ --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
            <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                📋 Audit / Related Documents
                <span class="ml-2 font-normal text-gray-400">(F-21 · SR-16 · SR-19)</span>
            </h3>
            <span class="text-xs text-gray-400">{{ $logs->total() }} entri</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-3 py-2 text-gray-500 font-medium w-36">Timestamp</th>
                        <th class="text-left px-3 py-2 text-gray-500 font-medium w-28">Document</th>
                        <th class="text-left px-3 py-2 text-gray-500 font-medium w-28">Tipe</th>
                        <th class="text-left px-3 py-2 text-gray-500 font-medium w-32">Actor (User)</th>
                        <th class="text-left px-3 py-2 text-gray-500 font-medium w-24">Employee ID</th>
                        <th class="text-left px-3 py-2 text-gray-500 font-medium w-32">Aksi</th>
                        <th class="text-left px-3 py-2 text-gray-500 font-medium">Deskripsi / Hasil</th>
                        <th class="text-center px-3 py-2 text-gray-500 font-medium w-16">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    @php
                        $actionCls = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-600';
                    @endphp
                    <tr class="border-b last:border-0 hover:bg-gray-50 transition">
                        {{-- Timestamp --}}
                        <td class="px-3 py-2 text-gray-500 whitespace-nowrap font-mono">
                            {{ \Carbon\Carbon::parse($log->acted_at)->format('d/m/Y H:i:s') }}
                        </td>
                        {{-- Document reference --}}
                        <td class="px-3 py-2">
                            <a href="{{ route('reporting.audit.show', [$log->document_type, $log->document_id]) }}"
                               class="font-mono font-semibold text-[#714B67] hover:underline">
                                {{ $log->document_id }}
                            </a>
                        </td>
                        {{-- Document type --}}
                        <td class="px-3 py-2">
                            <span class="font-mono text-gray-500 text-[10px] bg-gray-100 px-1.5 py-0.5 rounded">
                                {{ $log->document_type }}
                            </span>
                        </td>
                        {{-- Actor user --}}
                        <td class="px-3 py-2 font-mono text-gray-700">{{ $log->actor_user }}</td>
                        {{-- Actor employee --}}
                        <td class="px-3 py-2 font-mono text-gray-500">
                            {{ $log->actor_employee ?? '—' }}
                        </td>
                        {{-- Action badge --}}
                        <td class="px-3 py-2">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-mono font-semibold {{ $actionCls }}">
                                {{ $log->action }}
                            </span>
                        </td>
                        {{-- Description / result --}}
                        <td class="px-3 py-2 text-gray-600 max-w-xs truncate" title="{{ $log->description }}">
                            {{ $log->description ?: '—' }}
                        </td>
                        {{-- Detail link --}}
                        <td class="px-3 py-2 text-center">
                            <a href="{{ route('reporting.audit.show', [$log->document_type, $log->document_id]) }}"
                               class="text-[#714B67] hover:underline text-xs">
                                🔍
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-12 text-gray-400">
                            Tidak ada log audit yang cocok dengan filter yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="px-4 py-3 border-t bg-gray-50 flex items-center justify-between">
            <span class="text-xs text-gray-500">
                Menampilkan {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ $logs->total() }} entri
            </span>
            <div class="flex items-center gap-1 text-xs">
                @if($logs->onFirstPage())
                    <span class="px-2 py-1 rounded border border-gray-200 text-gray-400">‹ Prev</span>
                @else
                    <a href="{{ $logs->previousPageUrl() }}"
                       class="px-2 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">‹ Prev</a>
                @endif
                <span class="px-2 py-1 text-gray-500">
                    Hal {{ $logs->currentPage() }} / {{ $logs->lastPage() }}
                </span>
                @if($logs->hasMorePages())
                    <a href="{{ $logs->nextPageUrl() }}"
                       class="px-2 py-1 rounded border border-gray-300 text-gray-600 hover:bg-gray-100">Next ›</a>
                @else
                    <span class="px-2 py-1 rounded border border-gray-200 text-gray-400">Next ›</span>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- ⑤ Related Docs + Log Notes footer — S-18 ⑤ --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-3">
        <div class="flex items-center gap-6 text-xs text-gray-500">
            <span class="font-semibold text-gray-600">⑤ Related Docs</span>
            <span>|</span>
            <span>Log Notes</span>
            <span>|</span>
            <span>Audit / History</span>
            <span class="ml-auto text-gray-400 italic">
                Klik 🔍 pada baris untuk melihat relasi dokumen terkait (F-21 · T-35)
            </span>
        </div>
    </div>

    {{-- SR & Feature reference --}}
    <div class="text-[10px] text-gray-400 flex items-center gap-4">
        <span>S-18 · F-21 · F-22 · F-19</span>
        <span>SR-16 (idempotent posting) · SR-19 (traceability)</span>
        <span>T-35 (related docs) · T-36 (re-validate block)</span>
    </div>
</div>
@endsection
