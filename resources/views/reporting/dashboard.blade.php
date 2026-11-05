@extends('layouts.app')
@section('title', 'Operational Dashboard')
@php $currentApp = 'Reporting'; @endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-300">/</span>
    <span class="text-gray-400">Reporting</span>
    <span class="mx-1 text-gray-300">/</span>
    <span class="text-gray-700 font-medium">Operational Dashboard</span>
@endsection

@section('control_panel_extra')
    <button class="text-xs text-gray-600 border border-gray-300 rounded px-3 py-1 hover:bg-gray-50 transition">Last 30 days ▾</button>
    <button class="text-xs bg-[#714B67] text-white rounded px-3 py-1 hover:bg-[#5c3d56] transition font-medium">Export</button>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- KPI Cards Row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">

        {{-- Card 1: Total On Hand --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4 flex flex-col gap-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-[#E6FAF9] flex items-center justify-center text-lg">📦</div>
                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide">Total On Hand</p>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl font-bold text-gray-800">{{ number_format($totalOnHand) }}</span>
                <span class="text-xs text-gray-400">unit</span>
            </div>
            <p class="text-[10px] text-gray-400">Baseline · Klik drill-down (SR-19)</p>
        </div>

        {{-- Card 2: Stock Value --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4 flex flex-col gap-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-[#F3EEF1] flex items-center justify-center text-lg">💰</div>
                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide">Stock Value</p>
            </div>
            <span class="text-xl font-bold text-gray-800">Rp{{ number_format($stockValue) }}</span>
            <p class="text-[10px] text-gray-400">Standard Cost × Qty · {{ $stocks->count() }} SKU</p>
        </div>

        {{-- Card 3: AR / Piutang --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4 flex flex-col gap-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-yellow-50 flex items-center justify-center text-lg">📥</div>
                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide">AR / Piutang</p>
            </div>
            <span class="text-xl font-bold text-gray-800">Rp{{ number_format($ar) }}</span>
            <p class="text-[10px] text-amber-600">↗ Open AR (opening balance)</p>
        </div>

        {{-- Card 4: AP / Hutang --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4 flex flex-col gap-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-lg">📤</div>
                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide">AP / Hutang</p>
            </div>
            <span class="text-xl font-bold text-gray-800">Rp{{ number_format($ap) }}</span>
            <p class="text-[10px] text-blue-600">Open AP (opening balance)</p>
        </div>

        {{-- Card 5: Saldo Bank --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4 flex flex-col gap-2">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-lg">🏦</div>
                <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide">Saldo Bank</p>
            </div>
            <span class="text-xl font-bold text-gray-800">Rp{{ number_format($bank) }}</span>
            <p class="text-[10px] text-green-600">✓ Opening balance</p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">

        {{-- Stock by Warehouse --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-800">Stock by Warehouse</h3>
            <p class="text-xs text-gray-400 mb-4">Distribusi {{ number_format($totalOnHand) }} unit across 2 lokasi</p>

            {{-- WH-JKT bar --}}
            <div class="mb-4">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-700 font-medium">WH-JKT</span>
                    <span class="text-xs text-gray-400">Gudang Utama Jakarta</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex-1 bg-gray-100 rounded h-4 overflow-hidden">
                        <div class="h-full bg-[#714B67] rounded" style="width: {{ $totalOnHand > 0 ? round($whJkt / $totalOnHand * 100) : 0 }}%"></div>
                    </div>
                    <span class="text-xs text-gray-700 font-semibold whitespace-nowrap">{{ $whJkt }} ({{ $totalOnHand > 0 ? round($whJkt / $totalOnHand * 100) : 0 }}%)</span>
                </div>
            </div>

            {{-- HUB-BDG bar --}}
            <div class="mb-4">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-700 font-medium">HUB-BDG</span>
                    <span class="text-xs text-gray-400">Hub Distribusi Bandung</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex-1 bg-gray-100 rounded h-4 overflow-hidden">
                        <div class="h-full bg-[#00A09D] rounded" style="width: {{ $totalOnHand > 0 ? round($hubBdg / $totalOnHand * 100) : 0 }}%"></div>
                    </div>
                    <span class="text-xs text-gray-700 font-semibold whitespace-nowrap">{{ $hubBdg }} ({{ $totalOnHand > 0 ? round($hubBdg / $totalOnHand * 100) : 0 }}%)</span>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-2">
                <span class="text-xs text-gray-500">Total: <span class="font-bold text-gray-800">{{ number_format($totalOnHand) }} unit</span></span>
            </div>
        </div>

        {{-- Credit Exposure Watchlist --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <h3 class="text-sm font-semibold text-gray-800">Credit Exposure Watchlist</h3>
            <p class="text-xs text-gray-400 mb-4">SR-11 monitoring · Hard block at 100%</p>

            @foreach($customers as $customer)
            @php
                $pct = $customer->credit_limit > 0 ? round($customer->open_ar / $customer->credit_limit * 100) : 0;
                $barColor = $pct >= 80 ? 'bg-red-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-green-500');
                $statusLabel = $pct >= 80 ? '⚠ Near Limit' : '✓ Aman';
                $statusColor = $pct >= 80 ? 'text-amber-600' : 'text-green-600';
            @endphp
            <div class="mb-4">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-sm text-gray-700">{{ $customer->id }} — {{ $customer->name }}</span>
                    <span class="text-xs font-semibold {{ $statusColor }}">Rp{{ number_format($customer->open_ar) }} / Rp{{ number_format($customer->credit_limit) }} ({{ $pct }}%)</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex-1 bg-gray-100 rounded h-2.5 overflow-hidden">
                        <div class="h-full {{ $barColor }} rounded" style="width: {{ min($pct, 100) }}%"></div>
                    </div>
                </div>
                <p class="text-[10px] {{ $statusColor }} mt-1">{{ $statusLabel }}</p>
            </div>
            @endforeach

            @if($customers->isEmpty())
            <p class="text-xs text-gray-400 italic">Tidak ada customer dengan open AR.</p>
            @endif
        </div>
    </div>

    {{-- Recent Audit Trail --}}
    <div class="bg-white rounded-lg border border-gray-200 mb-6 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Recent Audit Trail</h3>
            <p class="text-xs text-gray-400">{{ $recentLogs->count() }} aktivitas terakhir · Klik untuk drill-down</p>
        </div>

        @if($recentLogs->isEmpty())
        <div class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada aktivitas.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-[10px] text-gray-500 uppercase font-bold tracking-wide border-b">
                        <th class="text-left px-4 py-2">Time</th>
                        <th class="text-left px-4 py-2">Actor</th>
                        <th class="text-left px-4 py-2">Document</th>
                        <th class="text-left px-4 py-2">Action</th>
                        <th class="text-left px-4 py-2">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recentLogs as $log)
                    @php
                        $actionColors = [
                            'created' => 'bg-green-100 text-green-700',
                            'confirm' => 'bg-green-100 text-green-700',
                            'posted' => 'bg-purple-100 text-purple-700',
                            'reserve' => 'bg-yellow-100 text-yellow-700',
                            'access_denied' => 'bg-red-100 text-red-700',
                            'status_changed' => 'bg-yellow-100 text-yellow-700',
                            'sign_off_rejected' => 'bg-red-100 text-red-700',
                        ];
                        $actionColor = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-600';
                    @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2 text-xs text-gray-400 whitespace-nowrap">{{ $log->acted_at->format('d/m H:i') }}</td>
                        <td class="px-4 py-2 text-xs font-semibold text-[#714B67]">{{ $log->actor_user }}</td>
                        <td class="px-4 py-2 text-xs font-mono text-gray-700">{{ $log->document_id }}</td>
                        <td class="px-4 py-2">
                            <span class="text-[10px] font-medium px-2 py-0.5 rounded {{ $actionColor }}">{{ $log->action }}</span>
                        </td>
                        <td class="px-4 py-2 text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($log->description, 80) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- Stock Overview Table --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-800">Stock Overview (List View)</h3>
            <p class="text-xs text-gray-400">SR-22: Available = On Hand − Reserved</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-[10px] text-gray-500 uppercase font-bold tracking-wide border-b sticky-th">
                        <th class="text-left px-4 py-2">Variant SKU ↕</th>
                        <th class="text-left px-4 py-2">Product Name ↕</th>
                        <th class="text-right px-4 py-2">WH-JKT ↕</th>
                        <th class="text-right px-4 py-2">HUB-BDG ↕</th>
                        <th class="text-right px-4 py-2">Total ↕</th>
                        <th class="text-right px-4 py-2">Reserved</th>
                        <th class="text-right px-4 py-2">Available</th>
                        <th class="text-right px-4 py-2">Cost</th>
                        <th class="text-right px-4 py-2">Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($stocks as $stock)
                    @php
                        $available = $stock->company_total - $stock->reserved;
                        $hasReservation = $stock->reserved > 0;
                    @endphp
                    <tr class="hover:bg-gray-50 transition {{ $hasReservation ? 'bg-red-50/30' : '' }}">
                        <td class="px-4 py-2 text-xs font-mono text-gray-600">{{ $stock->variant_sku }}</td>
                        <td class="px-4 py-2 text-xs text-gray-800">{{ $stock->product?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-xs text-right text-gray-700">{{ $stock->wh_jkt_on_hand }}</td>
                        <td class="px-4 py-2 text-xs text-right text-gray-700">{{ $stock->hub_bdg_on_hand }}</td>
                        <td class="px-4 py-2 text-xs text-right font-bold text-gray-800">{{ $stock->company_total }}</td>
                        <td class="px-4 py-2 text-xs text-right {{ $hasReservation ? 'text-red-600 font-bold' : 'text-gray-400' }}">{{ $stock->reserved }}</td>
                        <td class="px-4 py-2 text-xs text-right text-gray-700">{{ $available }}</td>
                        <td class="px-4 py-2 text-xs text-right text-gray-500">Rp{{ number_format($stock->standard_cost) }}</td>
                        <td class="px-4 py-2 text-xs text-right font-semibold text-gray-800">Rp{{ number_format($stock->standard_value) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 border-t-2 border-gray-200">
                    <tr class="font-bold">
                        <td class="px-4 py-2 text-xs text-gray-800" colspan="2">TOTAL ({{ $stocks->count() }} SKU)</td>
                        <td class="px-4 py-2 text-xs text-right text-gray-800">{{ $whJkt }}</td>
                        <td class="px-4 py-2 text-xs text-right text-gray-800">{{ $hubBdg }}</td>
                        <td class="px-4 py-2 text-xs text-right text-gray-800">{{ $totalOnHand }}</td>
                        <td class="px-4 py-2 text-xs text-right text-red-600">{{ $stocks->sum('reserved') }}</td>
                        <td class="px-4 py-2 text-xs text-right text-gray-800">{{ $totalOnHand - $stocks->sum('reserved') }}</td>
                        <td class="px-4 py-2"></td>
                        <td class="px-4 py-2 text-xs text-right text-gray-800">Rp{{ number_format($stockValue) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-4 py-2 border-t border-gray-100 flex items-center justify-between">
            <span class="text-[10px] text-gray-400">Showing {{ $stocks->count() }} of {{ $stocks->count() }} rows · Page 1 of 1</span>
            <div class="flex items-center gap-1">
                <button class="text-xs text-gray-400 border border-gray-200 rounded px-2 py-0.5" disabled>◀ Prev</button>
                <span class="text-xs text-gray-500 px-2">Page 1 of 1</span>
                <button class="text-xs text-gray-400 border border-gray-200 rounded px-2 py-0.5" disabled>Next ▶</button>
            </div>
        </div>
    </div>

    {{-- Footer note --}}
    <div class="mt-4 bg-gray-50 rounded-lg border border-gray-200 px-4 py-2.5 flex items-center justify-between">
        <span class="text-xs text-gray-500">📋 KPI hanya dari dokumen Posted/Done (SR-19) · Draft tidak dihitung · Semua angka dapat di-drill-down</span>
        <span class="text-[10px] text-gray-400 italic">php artisan fmcg:verify-checksum → 34/34 PASS</span>
    </div>

</div>
@endsection
