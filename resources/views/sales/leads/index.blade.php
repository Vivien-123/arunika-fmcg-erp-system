@extends('layouts.app')
@section('title','CRM Pipeline')
@php
    $currentApp  = 'CRM';
    $sidebarMenu = [
        ['label'=>'Pipeline',  'url'=>route('crm.leads.index'),      'icon'=>'📋'],
        ['label'=>'Buat Lead', 'url'=>route('crm.leads.index').'#new', 'icon'=>'➕'],
    ];
@endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-400">›</span>
    <a href="{{ route('crm.leads.index') }}" class="text-[#714B67] hover:underline">CRM</a>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700 font-medium">Pipeline</span>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-2">

    {{-- Header bar --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">① CRM Pipeline</h1>
            <p class="text-xs text-gray-500 mt-0.5">F-04 · S-04 · SR-19 · T-07, T-08</p>
        </div>
        <div class="flex gap-2">
            {{-- Views badge --}}
            <span class="bg-[#714B67] text-white text-xs px-3 py-1.5 rounded font-medium">Kanban</span>
            <span class="bg-white border text-gray-600 text-xs px-3 py-1.5 rounded">List</span>
            <a href="{{ route('crm.leads.index') }}" class="bg-white border text-gray-600 text-xs px-3 py-1.5 rounded hover:bg-gray-50">Form</a>
        </div>
    </div>

    {{-- Control Panel: Search / Filters --}}
    <form method="GET" class="bg-white border rounded-lg p-3 mb-4 flex flex-wrap gap-2 items-end">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-xs text-gray-500 mb-0.5">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="ID Lead / Pelanggan"
                   class="w-full border rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#714B67]">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-0.5">Stage</label>
            <select name="stage" class="border rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#714B67]">
                <option value="">Semua</option>
                <option value="new"       {{ ($filters['stage']??'')=='new'       ? 'selected' : '' }}>New</option>
                <option value="qualified" {{ ($filters['stage']??'')=='qualified' ? 'selected' : '' }}>Qualified</option>
                <option value="won"       {{ ($filters['stage']??'')=='won'       ? 'selected' : '' }}>Won</option>
                <option value="lost"      {{ ($filters['stage']??'')=='lost'      ? 'selected' : '' }}>Lost</option>
            </select>
        </div>
        <button type="submit" class="bg-[#714B67] text-white px-3 py-1.5 rounded text-sm hover:bg-[#5c3d56] transition">
            Terapkan
        </button>
        <a href="{{ route('crm.leads.index') }}" class="text-gray-500 text-sm px-3 py-1.5 hover:underline">Reset</a>
    </form>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded px-4 py-2 mb-4 text-sm">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded px-4 py-2 mb-4 text-sm">⛔ {{ session('error') }}</div>
    @endif

    {{-- ④ Kanban Board --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            'new'       => ['label'=>'New',       'color'=>'bg-gray-100 border-gray-300',    'badge'=>'bg-gray-200 text-gray-700'],
            'qualified' => ['label'=>'Qualified',  'color'=>'bg-blue-50 border-blue-200',     'badge'=>'bg-blue-200 text-blue-800'],
            'won'       => ['label'=>'Won',        'color'=>'bg-green-50 border-green-200',   'badge'=>'bg-green-200 text-green-800'],
            'lost'      => ['label'=>'Lost',       'color'=>'bg-red-50 border-red-200',       'badge'=>'bg-red-200 text-red-800'],
        ] as $stageKey => $stageConfig)
        <div class="rounded-xl border {{ $stageConfig['color'] }} p-3">
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-sm text-gray-700">{{ $stageConfig['label'] }}</h3>
                <span class="{{ $stageConfig['badge'] }} text-xs font-bold px-2 py-0.5 rounded-full">
                    {{ $stages[$stageKey]->count() }}
                </span>
            </div>

            <div class="space-y-2">
                @forelse($stages[$stageKey] as $lead)
                <a href="{{ route('crm.leads.show', $lead->id) }}"
                   class="block bg-white border rounded-lg p-3 shadow-sm hover:shadow-md hover:border-[#714B67] transition group">
                    <div class="flex items-start justify-between">
                        <span class="text-xs font-mono text-gray-500">{{ $lead->id }}</span>
                        @if($lead->partner_name)
                        <span class="text-xs bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded">C</span>
                        @else
                        <span class="text-xs bg-orange-100 text-orange-700 px-1.5 py-0.5 rounded">!</span>
                        @endif
                    </div>
                    <p class="text-sm font-medium text-gray-800 mt-1 group-hover:text-[#714B67]">
                        {{ $lead->partner_name ?? '— Tanpa Pelanggan —' }}
                    </p>
                    @if($lead->expected_revenue > 0)
                    <p class="text-xs text-gray-500 mt-1">
                        Potensi: <span class="font-semibold text-gray-700">Rp{{ number_format($lead->expected_revenue) }}</span>
                    </p>
                    @endif
                </a>
                @empty
                <p class="text-xs text-gray-400 italic text-center py-4">Tidak ada lead</p>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>

    {{-- Buat Lead baru --}}
    <div class="mt-6 bg-white border rounded-xl p-5" id="new">
        <h2 class="font-semibold text-gray-700 mb-3">➕ Buat Lead Baru</h2>
        <form method="POST" action="{{ route('crm.leads.store') }}" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div>
                <label class="block text-xs text-gray-500 mb-0.5">Pelanggan</label>
                <select name="partner_id" class="border rounded px-2 py-1.5 text-sm min-w-[200px] focus:ring-2 focus:ring-[#714B67] focus:outline-none">
                    <option value="">— Pilih Pelanggan —</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}">{{ $c->id }} — {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-0.5">Potensi Pendapatan (Rp)</label>
                <input type="number" name="expected_revenue" min="0" step="1000" placeholder="500000"
                       class="border rounded px-2 py-1.5 text-sm w-40 focus:ring-2 focus:ring-[#714B67] focus:outline-none">
            </div>
            <button type="submit" class="bg-[#714B67] text-white px-4 py-1.5 rounded text-sm hover:bg-[#5c3d56] transition">
                Buat Lead
            </button>
        </form>
    </div>

    {{-- ⑤ Log Notes hint --}}
    <div class="mt-3 text-xs text-gray-400 italic">
        ⑤ Related Docs | Log Notes | Audit / History — klik pada setiap kartu lead untuk melihat detail.
    </div>
</div>
@endsection
