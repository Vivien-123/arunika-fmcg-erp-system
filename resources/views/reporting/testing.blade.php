@extends('layouts.app')
@section('title', 'Integration Testing Monitor')
@php $currentApp = 'Integration Testing'; @endphp

@section('breadcrumb')
    <span class="mx-1">/</span><span class="text-gray-600 font-medium">Integration Testing</span>
@endsection

@section('control_panel_extra')
<button class="btn-primary text-xs flex items-center gap-1.5">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    Run scenarios
</button>
@endsection

@section('content')
<div class="max-w-5xl">

    {{-- Page header --}}
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-900">Integration testing monitor</h1>
        <p class="text-xs text-gray-400 mt-0.5">S-18 · F-21 · F-22 · {{ $total }} skenario · {{ $passed > 0 ? $passed.' passed' : 'Belum dieksekusi' }}</p>
    </div>

    {{-- Filter bar --}}
    <div class="panel mb-4">
        <div class="px-4 py-3 flex items-center gap-2 flex-wrap border-b border-gray-100">
            <input type="text" placeholder="Cari..." class="filter-input w-48">
            <select class="filter-input"><option>Semua status</option><option>Pass</option><option>Not Run</option><option>Failed</option></select>
            <select class="filter-input"><option>Group by</option><option>Module</option><option>Category</option></select>
            <select class="filter-input"><option>Kolom</option></select>
            <button class="filter-input text-gray-500">Export CSV</button>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-4 gap-3 mb-5">
        <div class="panel px-4 py-3">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide">Passed</p>
            <p class="text-3xl font-bold text-green-600 mt-1 tnum">{{ $passed }}</p>
        </div>
        <div class="panel px-4 py-3">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide">Failed</p>
            <p class="text-3xl font-bold text-red-500 mt-1 tnum">0</p>
        </div>
        <div class="panel px-4 py-3">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide">Blocked</p>
            <p class="text-3xl font-bold text-yellow-500 mt-1 tnum">0</p>
        </div>
        <div class="panel px-4 py-3">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide">Not Run</p>
            <p class="text-3xl font-bold text-gray-400 mt-1 tnum">{{ $total - $passed }}</p>
        </div>
    </div>

    {{-- Progress --}}
    <div class="panel px-5 py-4 mb-5">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-gray-700">{{ $passed }} / {{ $total }} skenario selesai</span>
            <span class="text-xs text-gray-400">Total: {{ $total }} · Progress: {{ round($passed/$total*100) }}%</span>
        </div>
        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
            <div class="h-full bg-[#714B67] rounded-full transition-all duration-500" style="width: {{ round($passed/$total*100) }}%"></div>
        </div>
        @if($passed === 0)
        <p class="text-xs text-gray-400 mt-2">Skenario rinci berikut adalah usulan turunan dari kelompok pengujian pada blueprint.</p>
        @endif
    </div>

    {{-- Table --}}
    @php
        $moduleColors = ['M1'=>'badge-yellow','M2'=>'badge-blue','M3'=>'badge-purple','M4'=>'badge-green'];
        $statusConfig = [
            'pass'    => ['class'=>'badge-green',  'label'=>'Pass'],
            'fail'    => ['class'=>'badge-red',    'label'=>'Failed'],
            'blocked' => ['class'=>'badge-yellow', 'label'=>'Blocked'],
            'not_run' => ['class'=>'badge-gray',   'label'=>'Not Run'],
        ];
    @endphp

    <div class="panel overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100">
            <h3 class="text-sm font-semibold text-gray-700">Testing matrix / T-01 — T-36</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-5 py-2.5 text-[10px] font-bold text-gray-400 uppercase w-8"></th>
                        <th class="text-left px-2 py-2.5 text-[10px] font-bold text-gray-400 uppercase w-8"></th>
                        <th class="text-left px-2 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide w-20">Test ID</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Skenario</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide w-20">Modul</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Expected result</th>
                        <th class="text-left px-4 py-2.5 text-[10px] font-bold text-gray-400 uppercase tracking-wide w-24">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($tests as $test)
                    @php
                        $sc = $statusConfig[$test['status']] ?? $statusConfig['not_run'];
                        $mc = $moduleColors[$test['module']] ?? 'badge-gray';
                        $expectedMap = [
                            'T-01'=>'Modul terotorisasi terlihat','T-02'=>'Akses ditolak',
                            'T-03'=>'C01 / C02 sesuai baseline','T-04'=>'Block bila order > 878.000',
                            'T-05'=>'16 SKU; data lengkap dibutuhkan','T-06'=>'Lot expiry terdekat dipilih',
                            'T-07'=>'Lead tercatat dengan owner','T-08'=>'Relasi sumber dipertahankan',
                            'T-09'=>'SO S00008 net Rp250.000, gross Rp277.500','T-10'=>'Finance approval diperlukan',
                            'T-11'=>'Qty tervalidasi; stok konsisten','T-12'=>'Backorder terbentuk',
                            'T-13'=>'On Hand 60 Reserved 8 Available 52','T-14'=>'Transfer blocked bila reserved',
                            'T-15'=>'PO net Rp640.000, gross Rp710.400','T-16'=>'Price edit ditolak',
                            'T-17'=>'WH P05=45, stock +Rp480.000','T-18'=>'Sign-off ditolak (E03 Nonaktif)',
                            'T-19'=>'Invoice net Rp250.000, gross Rp277.500','T-20'=>'Qty capped at delivered',
                            'T-21'=>'Bill net Rp480.000, AP Rp1.232.800','T-22'=>'Bill blocked bila qty melebihi receipt',
                            'T-23'=>'Outstanding Rp177.500 setelah bayar','T-24'=>'Overpayment ditolak',
                            'T-25'=>'Dr HPP = Cr Persediaan Rp170.000','T-26'=>'Double post diblokir',
                            'T-27'=>'5 dept, 8 employee, Warehouse 3','T-28'=>'Data privat hanya HR',
                            'T-29'=>'E01 boleh sign-off, E03 tidak','T-30'=>'Sign-off blok setelah Nonaktif',
                            'T-31'=>'Credit note Rp55.500, stok kembali','T-32'=>'CN qty ceiling ditegakkan',
                            'T-33'=>'On Hand 880, AR 1.5jt, AP 700k','T-34'=>'Draft tidak masuk KPI',
                            'T-35'=>'PO00007 Receipt=0, Bill=0','T-36'=>'Re-validate idempotent',
                        ];
                        $expected = $expectedMap[$test['id']] ?? $test['name'];
                    @endphp
                    <tr class="tr-hover">
                        <td class="px-5 py-3 text-gray-300">
                            <svg class="w-4 h-4 hover:text-[#714B67] cursor-pointer transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </td>
                        <td class="px-2 py-3">
                            <input type="checkbox" class="w-3.5 h-3.5 accent-[#714B67]">
                        </td>
                        <td class="px-2 py-3 font-mono font-bold text-[#714B67] text-[11px]">{{ $test['id'] }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $test['name'] }}</td>
                        <td class="px-4 py-3"><span class="badge {{ $mc }}">{{ $test['module'] }}</span></td>
                        <td class="px-4 py-3 text-gray-500">{{ $expected }}</td>
                        <td class="px-4 py-3"><span class="badge {{ $sc['class'] }}">{{ $sc['label'] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
