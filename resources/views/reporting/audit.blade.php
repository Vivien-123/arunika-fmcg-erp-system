@extends('layouts.app')
@section('title','Audit Log & System Trail')
@php
    $currentApp = 'Audit Log';

    $actionBadge = [
        'created'               => 'badge-gray',
        'login'                 => 'badge-blue',
        'logout'                => 'badge-gray',
        'status_changed'        => 'badge-yellow',
        'sign_off_authorized'   => 'badge-green',
        'sign_off_rejected'     => 'badge-red',
        'access_denied'         => 'badge-red',
        'private_access_denied' => 'badge-yellow',
        'confirm'               => 'badge-blue',
        'reserve'               => 'badge-blue',
        'validate'              => 'badge-green',
        'post'                  => 'badge-purple',
        'payment'               => 'badge-green',
        'return'                => 'badge-red',
        'rejected_edit'         => 'badge-red',
        'double_post_blocked'   => 'badge-red',
    ];

    $typeIcons = [
        'employee'         => 'E',
        'access_control'   => 'AC',
        'purchase_order'   => 'PO',
        'sales_order'      => 'SO',
        'delivery'         => 'DO',
        'receipt'          => 'RC',
        'customer_invoice' => 'INV',
        'vendor_bill'      => 'BILL',
        'payment'          => 'PAY',
        'credit_note'      => 'CN',
        'transfer'         => 'TRF',
        'lead'             => 'CRM',
    ];

    $hasActiveFilters = count($filters) > 0;
@endphp

@section('breadcrumb')
    <span class="mx-1">/</span>
    <span class="text-gray-600 font-medium">Audit Log</span>
@endsection

@section('control_panel_extra')
{{-- Active filter chips --}}
@if($hasActiveFilters)
<div class="flex items-center gap-1.5 flex-wrap">
    @foreach($filters as $key => $val)
    <span class="inline-flex items-center gap-1 text-[11px] font-medium bg-purple-50 text-purple-700 border border-purple-200 px-2 py-0.5 rounded">
        {{ str_replace('_',' ',$key) }}: <span class="font-mono">{{ $val }}</span>
        <a href="{{ route('reporting.audit', array_diff_key($filters, [$key => null])) }}" class="ml-0.5 hover:text-red-500">×</a>
    </span>
    @endforeach
    <a href="{{ route('reporting.audit') }}" class="text-[11px] text-gray-400 hover:text-red-500 transition">Reset</a>
</div>
@endif
<span class="flex-1"></span>

{{-- Filter button --}}
<button onclick="openFilterModal()"
        class="btn-secondary text-xs flex items-center gap-1.5 {{ $hasActiveFilters ? 'border-purple-300 text-purple-700 bg-purple-50' : '' }}">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
    </svg>
    Filter
    @if($hasActiveFilters)
    <span class="bg-[#714B67] text-white text-[9px] font-bold w-4 h-4 rounded-full flex items-center justify-center">{{ count($filters) }}</span>
    @endif
</button>

<a href="{{ route('reporting.testing') }}" class="btn-secondary text-xs">Integration Testing →</a>
@endsection

@section('content')

{{-- Page header --}}
<div class="mb-5 flex items-start justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-gray-900">Audit log &amp; system trail</h1>
        <p class="text-xs text-gray-400 mt-0.5">S-18 / Security · F-21 · F-22 · Read-only</p>
    </div>
</div>

{{-- Info banner --}}
<div class="alert-info mb-5 text-xs text-blue-800">
    <p class="font-semibold mb-0.5">Riwayat Immutable · Tidak ada Edit atau Delete</p>
    <p class="text-blue-600">Contoh tampilan audit, bukan aktivitas sistem yang sudah dieksekusi. Semua timestamp menggunakan WIB.</p>
</div>

{{-- Filter bar --}}
<div class="panel mb-4">
    <div class="px-4 py-3 flex items-center gap-2 flex-wrap border-b border-gray-100">
        <input type="text" placeholder="Cari..." class="filter-input w-48"
               id="searchInput" value="{{ $filters['document_ref'] ?? '' }}"
               onchange="syncSearch(this.value)">
        <select class="filter-input" id="typeSelect" onchange="syncType(this.value)">
            <option value="">Semua status</option>
            @foreach($distinctTypes as $dt)
            <option value="{{ $dt }}" {{ ($filters['document_type'] ?? '') === $dt ? 'selected' : '' }}>
                {{ ucfirst(str_replace('_',' ',$dt)) }}
            </option>
            @endforeach
        </select>
        <select class="filter-input" id="actionSelect" onchange="syncAction(this.value)">
            <option value="">Group by</option>
            @foreach($distinctActions as $act)
            <option value="{{ $act }}" {{ ($filters['action'] ?? '') === $act ? 'selected' : '' }}>{{ $act }}</option>
            @endforeach
        </select>
        <input type="text" placeholder="Kolom" class="filter-input w-28" readonly>
        <button onclick="exportAudit()" class="filter-input text-gray-500 hover:text-gray-700 cursor-pointer">Export CSV</button>
    </div>
</div>

{{-- KPI row --}}
<div class="grid grid-cols-4 gap-3 mb-5">
    @php
    $kpis = [
        ['label'=>'Total Entri',   'value'=> $totalCount,    'sub'=>'semua waktu'],
        ['label'=>'Hasil Filter',  'value'=> $filteredCount, 'sub'=> $hasActiveFilters ? 'filter aktif' : 'semua'],
        ['label'=>'Dok. Unik',     'value'=> \App\Models\AuditLog::query()->distinct()->count('document_id'), 'sub'=>'dokumen'],
        ['label'=>'Jenis Aksi',    'value'=> count($distinctActions), 'sub'=>'tipe berbeda'],
    ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="panel px-4 py-3">
        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide">{{ $kpi['label'] }}</p>
        <p class="text-2xl font-bold text-gray-800 mt-1 tnum">{{ number_format($kpi['value']) }}</p>
        <p class="text-[11px] text-gray-400 mt-0.5">{{ $kpi['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- Table --}}
<div class="panel overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-700">System events</h3>
        <span class="text-xs text-gray-400">{{ $logs->total() }} entri</span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-5 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Timestamp (WIB)</th>
                    <th class="text-left px-5 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Event ID</th>
                    <th class="text-left px-5 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Actor / Employee</th>
                    <th class="text-left px-5 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Modul</th>
                    <th class="text-left px-5 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Action</th>
                    <th class="text-left px-5 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Entity ID</th>
                    <th class="text-left px-5 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Result</th>
                    <th class="px-5 py-2.5 w-8"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($logs as $i => $log)
                @php
                    $badgeCls = $actionBadge[$log->action] ?? 'badge-gray';
                    $isBlocked = in_array($log->action, ['access_denied','sign_off_rejected','rejected_edit','double_post_blocked','private_access_denied']);
                    $isSuccess = in_array($log->action, ['confirm','validate','post','payment','login','sign_off_authorized','created']);
                    $resultText = $isBlocked ? 'Blocked' : ($isSuccess ? 'Success' : '—');
                    $resultCls  = $isBlocked ? 'text-red-600 font-semibold' : ($isSuccess ? 'text-green-600 font-semibold' : 'text-gray-400');
                    $typeTag    = $typeIcons[$log->document_type] ?? strtoupper(substr($log->document_type,0,3));
                @endphp
                <tr class="tr-hover cursor-pointer" onclick="showAuditDetail({{ $log->id }})">
                    <td class="px-5 py-3 font-mono text-gray-500 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($log->acted_at)->format('d M Y, H:i') }}
                    </td>
                    <td class="px-5 py-3 font-mono text-gray-400 text-[11px]">
                        EVT-{{ str_pad($log->id, 4, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="font-medium text-gray-700">{{ $log->actor_user }}</span>
                        @if($log->actor_employee)
                        <span class="text-gray-400"> / {{ $log->actor_employee }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="font-mono text-[11px] bg-purple-50 text-purple-700 px-1.5 py-0.5 rounded font-semibold">{{ $typeTag }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="badge {{ $badgeCls }}">{{ $log->action }}</span>
                    </td>
                    <td class="px-5 py-3">
                        <a href="{{ route('reporting.audit.show', [$log->document_type, $log->document_id]) }}"
                           class="font-mono text-[#714B67] hover:underline" onclick="event.stopPropagation()">
                            {{ $log->document_id }}
                        </a>
                    </td>
                    <td class="px-5 py-3 {{ $resultCls }}">{{ $resultText }}</td>
                    <td class="px-5 py-3 text-center">
                        <a href="{{ route('reporting.audit.show', [$log->document_type, $log->document_id]) }}"
                           onclick="event.stopPropagation()"
                           class="text-gray-300 hover:text-[#714B67] transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-16 text-center">
                        <p class="text-gray-400 text-sm">Tidak ada data audit yang cocok.</p>
                        @if($hasActiveFilters)
                        <a href="{{ route('reporting.audit') }}" class="text-[#714B67] text-xs hover:underline mt-2 inline-block">Reset filter</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between bg-gray-50/50">
        <span class="text-xs text-gray-400">
            {{ $logs->firstItem() }}–{{ $logs->lastItem() }} dari {{ number_format($logs->total()) }} entri
        </span>
        <div class="flex items-center gap-1">
            @if($logs->onFirstPage())
            <span class="btn-secondary text-xs opacity-40 cursor-default px-3 py-1.5">‹</span>
            @else
            <a href="{{ $logs->previousPageUrl() }}" class="btn-secondary text-xs px-3 py-1.5">‹</a>
            @endif
            <span class="text-xs text-gray-500 px-2">{{ $logs->currentPage() }} / {{ $logs->lastPage() }}</span>
            @if($logs->hasMorePages())
            <a href="{{ $logs->nextPageUrl() }}" class="btn-secondary text-xs px-3 py-1.5">›</a>
            @else
            <span class="btn-secondary text-xs opacity-40 cursor-default px-3 py-1.5">›</span>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- Feature reference --}}
<p class="text-[10px] text-gray-300 mt-4">S-18 · F-21 · SR-16 (idempotent) · SR-19 (traceability) · T-35 (related docs) · T-36 (re-validate block)</p>

@endsection

{{-- ═════ FILTER MODAL ═════ --}}
<div id="filterModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     onclick="if(event.target===this)closeFilterModal()">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-lg shadow-2xl w-full max-w-md border border-gray-200" style="animation:modalIn .18s ease-out">

        <div class="bg-[#714B67] px-6 py-4 rounded-t-lg flex items-center justify-between">
            <div>
                <h2 class="text-white font-semibold text-sm flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Filter Audit Log
                </h2>
                <p class="text-purple-200 text-xs mt-0.5">Sempitkan hasil berdasarkan kriteria berikut</p>
            </div>
            <button onclick="closeFilterModal()" class="text-white/70 hover:text-white p-1 rounded hover:bg-white/10 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="GET" action="{{ route('reporting.audit') }}" class="px-6 py-5 space-y-4">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <span class="text-base mr-1">📌</span> Nomor Dokumen
                    </label>
                    <input type="text" name="document_ref" value="{{ $filters['document_ref'] ?? '' }}"
                           placeholder="Contoh: S00007, PO00007"
                           class="form-input w-full text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <span class="text-base mr-1">📁</span> Tipe Dokumen
                    </label>
                    <select name="document_type" class="form-input w-full text-xs">
                        <option value="">Semua Tipe</option>
                        @foreach($distinctTypes as $dt)
                        <option value="{{ $dt }}" {{ ($filters['document_type'] ?? '') === $dt ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_',' ',$dt)) }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <span class="text-base mr-1">👤</span> Actor / Employee ID
                    </label>
                    <input type="text" name="actor" value="{{ $filters['actor'] ?? '' }}"
                           placeholder="Contoh: sales_user, E01"
                           class="form-input w-full text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        <span class="text-base mr-1">⚡</span> Jenis Aksi
                    </label>
                    <select name="action" class="form-input w-full text-xs">
                        <option value="">Semua Aksi</option>
                        @foreach($distinctActions as $act)
                        <option value="{{ $act }}" {{ ($filters['action'] ?? '') === $act ? 'selected' : '' }}>{{ $act }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    <span class="text-base mr-1">📅</span> Rentang Waktu
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <div class="relative">
                        <span class="absolute top-0 left-0 text-[10px] text-gray-400 font-semibold -mt-4">Dari</span>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                               class="form-input w-full text-xs">
                    </div>
                    <div class="relative">
                        <span class="absolute top-0 left-0 text-[10px] text-gray-400 font-semibold -mt-4">Sampai</span>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                               class="form-input w-full text-xs">
                    </div>
                </div>
            </div>

            {{-- Quick presets --}}
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-2">Filter Cepat</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" onclick="setF('document_type','purchase_order')" class="text-[11px] px-3 py-1 rounded-full border border-gray-200 text-gray-500 hover:border-[#714B67] hover:text-[#714B67] transition">📦 Purchase Order</button>
                    <button type="button" onclick="setF('document_type','sales_order')" class="text-[11px] px-3 py-1 rounded-full border border-gray-200 text-gray-500 hover:border-[#714B67] hover:text-[#714B67] transition">🛒 Sales Order</button>
                    <button type="button" onclick="setF('document_type','access_control')" class="text-[11px] px-3 py-1 rounded-full border border-gray-200 text-gray-500 hover:border-[#714B67] hover:text-[#714B67] transition">🔐 Access Control</button>
                    <button type="button" onclick="setF('action','access_denied')" class="text-[11px] px-3 py-1 rounded-full border border-red-200 text-red-500 hover:bg-red-50 transition">⛔ Access Denied</button>
                    <button type="button" onclick="setF('action','sign_off_rejected')" class="text-[11px] px-3 py-1 rounded-full border border-red-200 text-red-500 hover:bg-red-50 transition">🚫 Sign-off Rejected</button>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4 flex items-center justify-between">
                <a href="{{ route('reporting.audit') }}" class="text-xs text-gray-400 hover:text-gray-600 flex items-center gap-1 transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Reset Filter
                </a>
                <div class="flex gap-2">
                    <button type="button" onclick="closeFilterModal()" class="btn-secondary text-xs">Batal</button>
                    <button type="submit" class="btn-primary text-xs flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes modalIn { from{opacity:0;transform:scale(.96) translateY(-6px)} to{opacity:1;transform:scale(1) translateY(0)} }
</style>
<script>
function openFilterModal(){
    const m=document.getElementById('filterModal');
    m.classList.remove('hidden'); m.classList.add('flex');
    document.body.style.overflow='hidden';
}
function closeFilterModal(){
    const m=document.getElementById('filterModal');
    m.classList.add('hidden'); m.classList.remove('flex');
    document.body.style.overflow='';
}
function setF(name,val){ document.querySelector('[name="'+name+'"]').value=val; }
document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeFilterModal(); });
function showAuditDetail(id){ /* future: open side drawer */ }
function syncSearch(v){ /* handled by form submit */ }
function syncType(v){ document.querySelector('[name="action"]'); }
function syncAction(v){ /* handled by form submit */ }
function exportAudit(){ alert('Export CSV — fitur segera hadir'); }
</script>
