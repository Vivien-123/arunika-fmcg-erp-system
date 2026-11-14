@extends('layouts.app')
@section('title', 'Inventory & Stock Verification')
@php $currentApp = 'Inventory'; @endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-300">/</span><span class="text-gray-400">Inventory &amp; Stock</span>
    <span class="mx-1 text-gray-300">/</span><span class="text-gray-700 font-medium">Stock Overview (S-03, S-06, S-07)</span>
@endsection

@section('control_panel_extra')
    <input type="text" placeholder="Cari SKU atau nama produk..." class="text-xs border border-gray-300 rounded px-3 py-1 w-48 focus:outline-none focus:border-[#714B67]">
    <button class="text-xs text-gray-600 border border-gray-300 rounded px-3 py-1 hover:bg-gray-50 transition">Filters ▾</button>
    <button class="text-xs text-gray-600 border border-gray-300 rounded px-3 py-1 hover:bg-gray-50 transition">Group By ▾</button>
    @if(auth()->user()->canEdit())
    <a href="{{ route('inventory.transfer') }}" class="text-xs bg-[#714B67] text-white rounded px-3 py-1 hover:bg-[#5E3D56] transition font-medium">+ Transfer</a>
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
            <p class="text-[10px] text-gray-400 mt-1">Perpetual standard cost · {{ $stocks->count() }} SKU</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide mb-1">Reserved (S00007)</p>
            <span class="text-xl font-bold text-red-600 tnum">{{ $stocks->sum('reserved') }}</span>
            <p class="text-[10px] text-gray-400 mt-1">SR-22: Available = On Hand - Reserved</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide mb-1">Available</p>
            <span class="text-xl font-bold text-green-600 tnum">{{ $totalOnHand - $stocks->sum('reserved') }}</span>
            <p class="text-[10px] text-gray-400 mt-1">Free to allocate</p>
        </div>
    </div>

    {{-- Warehouse Location Cards (S-06) --}}
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

    {{-- Baseline Stock Table (S-07) --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-700">Baseline Stock Matrix — 16 Variant SKU FMCG</h2>
                <p class="text-xs text-gray-400 mt-0.5">SR-04: No negative stock · SR-22: Available = On Hand - Reserved</p>
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
                <tbody class="divide-y divide-gray-50">
                    @foreach($stocks as $stock)
                    @php $available = $stock->company_total - $stock->reserved; $hasRes = $stock->reserved > 0; @endphp
                    <tr class="hover:bg-gray-50 transition {{ $hasRes ? 'bg-red-50/30' : '' }}">
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
            <h2 class="text-sm font-semibold text-gray-700">FEFO Lot Tracking &amp; Expiry Panel (SR-09)</h2>
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
    <div class="bg-white rounded-lg border border-gray-200 p-5 mb-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Internal Transfer (SR-14) — WH-JKT to HUB-BDG</h2>
        <p class="text-xs text-gray-400 mb-4">Mengurangi WH-JKT, menambah HUB-BDG · Nilai stok total &amp; PPN tetap · SR-04: Tidak boleh melebihi Available</p>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="text-xs text-gray-500 font-semibold">Source Location</label>
                <select class="w-full mt-1 border border-gray-300 rounded px-3 py-1.5 text-sm"><option>WH-JKT — Gudang Utama Jakarta</option></select>
            </div>
            <div>
                <label class="text-xs text-gray-500 font-semibold">Destination</label>
                <select class="w-full mt-1 border border-gray-300 rounded px-3 py-1.5 text-sm"><option>HUB-BDG — Hub Distribusi Bandung</option></select>
            </div>
            <div>
                <label class="text-xs text-gray-500 font-semibold">Product SKU</label>
                <select class="w-full mt-1 border border-gray-300 rounded px-3 py-1.5 text-sm">
                    @foreach($stocks as $s)<option>{{ $s->variant_sku }} — {{ $s->product?->name }} (Avail: {{ $s->company_total - $s->reserved }})</option>@endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 font-semibold">Quantity</label>
                <input type="number" min="1" class="w-full mt-1 border border-gray-300 rounded px-3 py-1.5 text-sm" placeholder="0">
            </div>
        </div>
        <button class="mt-4 bg-[#714B67] text-white text-sm font-medium px-4 py-2 rounded hover:bg-[#5E3D56] transition">Create Transfer</button>
    </div>

</div>
@endsection
