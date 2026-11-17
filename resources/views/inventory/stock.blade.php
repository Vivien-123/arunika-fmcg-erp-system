@extends('layouts.app')
@section('title', 'Inventory & Stock')
@php $currentApp = 'Inventory'; @endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-300">/</span><span class="text-gray-400">Inventory &amp; Stock</span>
    <span class="mx-1 text-gray-300">/</span><span class="text-gray-700 font-medium">Stock Overview</span>
@endsection

@section('control_panel_extra')
    <input type="text" id="stockSearch" oninput="applyStockFilters()" placeholder="Cari SKU atau nama produk..."
           class="text-xs border border-gray-300 rounded px-3 py-1 w-52 shrink-0 focus:outline-none focus:border-[#714B67]">

    {{-- Filters --}}
    <div class="relative shrink-0">
        <button type="button" onclick="toggleStockPanel('filterPanel')"
                class="text-xs text-gray-600 border border-gray-300 rounded px-3 py-1 hover:bg-gray-50 transition flex items-center gap-1 whitespace-nowrap">
            Filters <span id="filterBadge" class="hidden bg-[#714B67] text-white text-[9px] font-bold w-4 h-4 rounded-full items-center justify-center">!</span>
            <span class="text-[9px]">▾</span>
        </button>
        <div id="filterPanel" class="hidden absolute right-0 mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow-lg p-3 z-30 space-y-3 text-left">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">Lokasi</p>
                <select id="stockLocation" onchange="applyStockFilters()"
                        class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:border-[#714B67]">
                    <option value="">Semua lokasi</option>
                    <option value="WH-JKT">WH-JKT (stok > 0)</option>
                    <option value="HUB-BDG">HUB-BDG (stok > 0)</option>
                </select>
            </div>
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">Status stok</p>
                <select id="stockStatus" onchange="applyStockFilters()"
                        class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:border-[#714B67]">
                    <option value="">Semua status</option>
                    <option value="reserved">Ada reserved</option>
                    <option value="free">Tanpa reserved</option>
                    <option value="empty">Stok kosong</option>
                </select>
            </div>
            <button type="button" onclick="resetStockFilters()"
                    class="w-full text-xs text-red-600 border border-red-200 bg-red-50 rounded px-2 py-1.5 hover:bg-red-100 transition">
                Reset filter
            </button>
        </div>
    </div>

    {{-- Group By --}}
    <div class="relative shrink-0">
        <button type="button" onclick="toggleStockPanel('groupPanel')"
                class="text-xs text-gray-600 border border-gray-300 rounded px-3 py-1 hover:bg-gray-50 transition flex items-center gap-1 whitespace-nowrap">
            Group By <span class="text-[9px]">▾</span>
        </button>
        <div id="groupPanel" class="hidden absolute right-0 mt-1 w-44 bg-white border border-gray-200 rounded-lg shadow-lg p-2 z-30 text-left">
            @foreach(['none'=>'Tanpa grup','sku'=>'SKU','name'=>'Nama produk','value'=>'Nilai stok','reserved'=>'Reserved'] as $val => $label)
            <button type="button" onclick="setStockGroup('{{ $val }}')"
                    class="w-full text-left text-xs px-2 py-1.5 rounded hover:bg-gray-50 transition stock-group-option" data-group="{{ $val }}">
                {{ $label }}
            </button>
            @endforeach
        </div>
    </div>

    @if(in_array(auth()->user()->role, ['Warehouse', 'MasterAdmin'], true))
    <a href="#internal-transfer" class="text-xs bg-[#714B67] text-white rounded px-3 py-1 hover:bg-[#5E3D56] transition font-medium">+ Transfer</a>
    @endif
@endsection

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- KPI Summary Bar --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide mb-1">Total On Hand</p>
            <div class="flex items-baseline gap-1"><span class="text-2xl font-bold text-gray-800 tnum">{{ number_format($totalOnHand) }}</span><span class="text-xs text-gray-400">unit</span></div>
            <p class="text-[10px] text-gray-400 mt-1">WH-JKT: {{ $whJkt }} · HUB-BDG: {{ $hubBdg }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide mb-1">Stock Valuation</p>
            <span class="text-xl font-bold text-gray-800 tnum">Rp{{ number_format($stockValue) }}</span>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide mb-1">Reserved</p>
            <span class="text-xl font-bold text-red-600 tnum">{{ $stocks->sum('reserved') }}</span>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide mb-1">Available</p>
            <span class="text-xl font-bold text-green-600 tnum">{{ $totalOnHand - $stocks->sum('reserved') }}</span>
        </div>
    </div>

    {{-- Warehouse location cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        @foreach($warehouses as $wh)
        @php $whTotal = $stocks->sum($wh->code === 'WH-JKT' ? 'wh_jkt_on_hand' : 'hub_bdg_on_hand'); @endphp
        <div class="bg-white rounded-lg border border-gray-200 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg {{ $wh->code === 'WH-JKT' ? 'bg-purple-100' : 'bg-teal-50' }} flex items-center justify-center text-2xl">🏭</div>
            <div class="flex-1">
                <div class="flex items-center gap-2">
                    <span class="text-sm font-semibold text-gray-800">{{ $wh->code }}</span>
                    <span class="text-xs text-gray-400">{{ $wh->name }}</span>
                </div>
                <p class="text-xs text-gray-500">{{ $wh->city }} · {{ $wh->usage }}</p>
            </div>
            <div class="text-right">
                <p class="text-lg font-bold text-gray-800 tnum">{{ $whTotal }}</p>
                <p class="text-[10px] text-gray-400">unit</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Stock table --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-700">Rincian Stok Produk</h2>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-[10px] uppercase font-bold text-gray-500 tracking-wide border-b sticky-th">
                        <th class="text-left px-4 py-2">SKU</th>
                        <th class="text-left px-4 py-2">Product Name</th>
                        <th class="text-left px-4 py-2">Unit</th>
                        <th class="text-right px-4 py-2">WH-JKT</th>
                        <th class="text-right px-4 py-2">HUB-BDG</th>
                        <th class="text-right px-4 py-2">Total</th>
                        <th class="text-right px-4 py-2">Reserved</th>
                        <th class="text-right px-4 py-2">Available</th>
                        <th class="text-right px-4 py-2">Cost (Rp)</th>
                        <th class="text-right px-4 py-2">Value (Rp)</th>
                    </tr>
                </thead>
                <tbody id="stockRows" class="divide-y divide-gray-50">
                    @foreach($stocks as $stock)
                    @php $available = $stock->company_total - $stock->reserved; $hasRes = $stock->reserved > 0; @endphp
                    <tr class="hover:bg-gray-50 transition {{ $hasRes ? 'bg-red-50/30' : '' }}"
                        data-sku="{{ $stock->variant_sku }}"
                        data-name="{{ $stock->product?->name ?? '' }}"
                        data-wh="{{ $stock->wh_jkt_on_hand }}"
                        data-hub="{{ $stock->hub_bdg_on_hand }}"
                        data-total="{{ $stock->company_total }}"
                        data-reserved="{{ $stock->reserved }}"
                        data-value="{{ $stock->standard_value }}">
                        <td class="px-4 py-2 text-xs mono text-gray-600">{{ $stock->variant_sku }}</td>
                        <td class="px-4 py-2 text-xs text-gray-800">{{ $stock->product?->name ?? '--' }}</td>
                        <td class="px-4 py-2 text-xs text-gray-500">{{ $stock->product?->unit ?? '--' }}</td>
                        <td class="px-4 py-2 text-xs text-right tnum text-gray-700">{{ $stock->wh_jkt_on_hand }}</td>
                        <td class="px-4 py-2 text-xs text-right tnum text-gray-700">{{ $stock->hub_bdg_on_hand }}</td>
                        <td class="px-4 py-2 text-xs text-right tnum font-bold text-gray-800">{{ $stock->company_total }}</td>
                        <td class="px-4 py-2 text-xs text-right tnum {{ $hasRes ? 'text-red-600 font-bold' : 'text-gray-400' }}">{{ $stock->reserved }}</td>
                        <td class="px-4 py-2 text-xs text-right tnum text-gray-700">{{ $available }}</td>
                        <td class="px-4 py-2 text-xs text-right tnum text-gray-500">{{ number_format($stock->standard_cost) }}</td>
                        <td class="px-4 py-2 text-xs text-right tnum font-semibold text-gray-800">{{ number_format($stock->standard_value) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr class="font-bold">
                        <td class="px-4 py-2.5 text-xs text-gray-800" colspan="3">TOTAL ({{ $stocks->count() }} SKU)</td>
                        <td class="px-4 py-2.5 text-xs text-right tnum text-gray-800">{{ $whJkt }}</td>
                        <td class="px-4 py-2.5 text-xs text-right tnum text-gray-800">{{ $hubBdg }}</td>
                        <td class="px-4 py-2.5 text-xs text-right tnum text-gray-800">{{ $totalOnHand }}</td>
                        <td class="px-4 py-2.5 text-xs text-right tnum text-red-600">{{ $stocks->sum('reserved') }}</td>
                        <td class="px-4 py-2.5 text-xs text-right tnum text-gray-800">{{ $totalOnHand - $stocks->sum('reserved') }}</td>
                        <td class="px-4 py-2.5"></td>
                        <td class="px-4 py-2.5 text-xs text-right tnum text-gray-800">Rp{{ number_format($stockValue) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- FEFO Lot Tracking (S-08) --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Pelacakan Lot dan Tanggal Kedaluwarsa</h2>
            <p class="text-xs text-gray-400 mt-0.5">First Expired First Out · Lot kedaluwarsa dilarang dikirim</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-[10px] uppercase font-bold text-gray-500 tracking-wide border-b">
                        <th class="text-left px-4 py-2">Lot ID</th>
                        <th class="text-left px-4 py-2">SKU</th>
                        <th class="text-left px-4 py-2">Product</th>
                        <th class="text-left px-4 py-2">Location</th>
                        <th class="text-right px-4 py-2">Qty</th>
                        <th class="text-left px-4 py-2">Expiry Date</th>
                        <th class="text-center px-4 py-2">FEFO Rank</th>
                        <th class="text-center px-4 py-2">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($lots as $i => $lot)
                    @php
                        $isExpired = $lot->expiry_date < now();
                        $daysLeft = $lot->expiry_date->diffInDays(now());
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2.5 text-xs mono font-bold text-[#714B67]">{{ $lot->lot_id }}</td>
                        <td class="px-4 py-2.5 text-xs mono text-gray-600">{{ $lot->variant_sku }}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-700">{{ $lot->product?->name ?? '--' }}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-600">{{ $lot->location }}</td>
                        <td class="px-4 py-2.5 text-xs text-right tnum font-semibold text-gray-800">{{ $lot->qty }}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-600">{{ $lot->expiry_date->format('d M Y') }}</td>
                        <td class="px-4 py-2.5 text-center"><span class="text-[10px] font-bold px-2 py-0.5 rounded {{ $i === 0 ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500' }}">{{ $i + 1 }}</span></td>
                        <td class="px-4 py-2.5 text-center">
                            @if($isExpired)
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-red-100 text-red-700">EXPIRED</span>
                            @elseif($daysLeft <= 30)
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-yellow-100 text-yellow-700">{{ $daysLeft }}d left</span>
                            @else
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-green-100 text-green-700">OK</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Internal Transfer Form (SR-14) --}}
    <div id="internal-transfer" class="bg-white rounded-lg border border-gray-200 p-5 mb-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Transfer Antar Gudang</h2>

        @if(in_array(auth()->user()->role, ['Warehouse', 'MasterAdmin'], true))
        <form method="POST" action="{{ route('inventory.transfer.store') }}" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="text-xs text-gray-500 font-semibold">Source Location</label>
                    <select name="from_location" required
                            class="w-full mt-1 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-[#714B67]">
                        <option value="WH-JKT">WH-JKT — Gudang Utama Jakarta</option>
                        <option value="HUB-BDG">HUB-BDG — Hub Distribusi Bandung</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500 font-semibold">Destination</label>
                    <select name="to_location" required
                            class="w-full mt-1 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-[#714B67]">
                        <option value="HUB-BDG">HUB-BDG — Hub Distribusi Bandung</option>
                        <option value="WH-JKT">WH-JKT — Gudang Utama Jakarta</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500 font-semibold">Product SKU</label>
                    <select name="variant_sku" required
                            class="w-full mt-1 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-[#714B67]">
                        @foreach($stocks as $s)
                        <option value="{{ $s->variant_sku }}">{{ $s->variant_sku }} — {{ $s->product?->name }} (Avail: {{ $s->company_total - $s->reserved }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-gray-500 font-semibold">Quantity</label>
                    <input type="number" name="qty" min="1" required placeholder="0"
                           class="w-full mt-1 border border-gray-300 rounded px-3 py-1.5 text-sm focus:outline-none focus:border-[#714B67]">
                </div>
            </div>
            <button type="submit"
                    class="bg-[#714B67] text-white text-sm font-medium px-4 py-2 rounded hover:bg-[#5E3D56] transition">
                Create Transfer
            </button>
        </form>
        @else
        <div class="bg-gray-50 border border-gray-200 rounded-lg px-4 py-3 text-xs text-gray-500">
            Role Anda tidak memiliki izin Internal Transfer (hanya Warehouse & Master Admin).
        </div>
        @endif
    </div>

</div>

<script>
function toggleStockPanel(id) {
    ['filterPanel', 'groupPanel'].forEach(function (panelId) {
        const panel = document.getElementById(panelId);
        if (!panel) return;
        if (panelId === id) panel.classList.toggle('hidden');
        else panel.classList.add('hidden');
    });
}

function applyStockFilters() {
    const term     = (document.getElementById('stockSearch')?.value || '').toLowerCase().trim();
    const location = document.getElementById('stockLocation')?.value || '';
    const status   = document.getElementById('stockStatus')?.value || '';

    let visible = 0;

    document.querySelectorAll('#stockRows tr').forEach(function (row) {
        const sku      = (row.dataset.sku || '').toLowerCase();
        const name     = (row.dataset.name || '').toLowerCase();
        const wh       = parseInt(row.dataset.wh || '0', 10);
        const hub      = parseInt(row.dataset.hub || '0', 10);
        const total    = parseInt(row.dataset.total || '0', 10);
        const reserved = parseInt(row.dataset.reserved || '0', 10);

        let show = !term || sku.includes(term) || name.includes(term);

        if (location === 'WH-JKT' && wh <= 0) show = false;
        if (location === 'HUB-BDG' && hub <= 0) show = false;

        if (status === 'reserved' && reserved <= 0) show = false;
        if (status === 'free'     && reserved > 0)  show = false;
        if (status === 'empty'    && total > 0)     show = false;

        row.classList.toggle('hidden', !show);
        if (show) visible++;
    });

    const emptyRow = document.getElementById('stockEmptyRow');
    if (emptyRow) emptyRow.classList.toggle('hidden', visible > 0);

    const badge = document.getElementById('filterBadge');
    if (badge) {
        const active = (location ? 1 : 0) + (status ? 1 : 0) + (term ? 1 : 0);
        badge.classList.toggle('hidden', active === 0);
        badge.classList.toggle('inline-flex', active > 0);
    }
}

function resetStockFilters() {
    const search = document.getElementById('stockSearch');
    if (search) search.value = '';
    const location = document.getElementById('stockLocation');
    if (location) location.value = '';
    const status = document.getElementById('stockStatus');
    if (status) status.value = '';
    applyStockFilters();
}

function setStockGroup(group) {
    const rows = Array.from(document.querySelectorAll('#stockRows tr'));

    const compare = {
        none:     null,
        sku:      (a, b) => a.dataset.sku.localeCompare(b.dataset.sku),
        name:     (a, b) => (a.dataset.name || '').localeCompare(b.dataset.name || ''),
        value:    (a, b) => parseInt(b.dataset.value || '0', 10) - parseInt(a.dataset.value || '0', 10),
        reserved: (a, b) => parseInt(b.dataset.reserved || '0', 10) - parseInt(a.dataset.reserved || '0', 10),
    }[group];

    if (compare) rows.sort(compare);

    const tbody = document.getElementById('stockRows');
    if (tbody) rows.forEach(row => tbody.appendChild(row));

    const panel = document.getElementById('groupPanel');
    if (panel) panel.classList.add('hidden');

    document.querySelectorAll('.stock-group-option').forEach(function (btn) {
        btn.classList.toggle('bg-purple-50', btn.dataset.group === group);
        btn.classList.toggle('text-[#714B67]', btn.dataset.group === group);
        btn.classList.toggle('font-semibold', btn.dataset.group === group);
    });
}

document.addEventListener('click', function (event) {
    if (event.target.closest('#filterPanel') || event.target.closest('#groupPanel')) return;
    if (event.target.closest('[onclick*="toggleStockPanel"]')) return;
    document.getElementById('filterPanel')?.classList.add('hidden');
    document.getElementById('groupPanel')?.classList.add('hidden');
});
</script>
@endsection
