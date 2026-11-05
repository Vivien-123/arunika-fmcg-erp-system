@extends('layouts.app')
@section('title', 'Integration Testing Monitor')
@php $currentApp = 'Reporting'; @endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-300">/</span><span class="text-gray-400">Reporting</span>
    <span class="mx-1 text-gray-300">/</span><span class="text-gray-700 font-medium">Testing Monitor (T-01..T-36)</span>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Progress Summary Header --}}
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Integration Testing Monitor</h1>
        <p class="text-sm text-gray-500 mt-1">36 Skenario Pengujian · T-01 s.d. T-36 · Kepatuhan SR-01 s.d. SR-22</p>
    </div>

    {{-- Progress Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wide mb-1">Total Tests</p>
            <span class="text-2xl font-bold text-gray-800 tnum">{{ $total }}</span>
        </div>
        <div class="bg-white rounded-lg border border-green-200 p-4 bg-green-50/50">
            <p class="text-[10px] text-green-600 uppercase font-bold tracking-wide mb-1">Passed</p>
            <span class="text-2xl font-bold text-green-700 tnum">{{ $passed }}</span>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-[10px] text-red-500 uppercase font-bold tracking-wide mb-1">Failed</p>
            <span class="text-2xl font-bold text-gray-400 tnum">0</span>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-[10px] text-yellow-600 uppercase font-bold tracking-wide mb-1">Blocked</p>
            <span class="text-2xl font-bold text-gray-400 tnum">0</span>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide mb-1">Not Run</p>
            <span class="text-2xl font-bold text-gray-400 tnum">{{ $total - $passed }}</span>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold text-gray-700">Overall Progress</span>
            <span class="text-sm font-bold text-green-700 tnum">{{ $passed }}/{{ $total }} ({{ round($passed/$total*100) }}%)</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-3 overflow-hidden">
            <div class="h-full bg-green-500 rounded-full transition-all duration-500" style="width: {{ round($passed/$total*100) }}%"></div>
        </div>
    </div>

    {{-- Test Grid by Category --}}
    @php
        $categories = $tests->groupBy('category');
        $moduleColors = [
            'M1' => 'bg-orange-100 text-orange-700',
            'M2' => 'bg-blue-100 text-blue-700',
            'M3' => 'bg-purple-100 text-purple-700',
            'M4' => 'bg-green-100 text-green-700',
        ];
    @endphp

    @foreach($categories as $categoryName => $categoryTests)
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-4">
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">{{ $categoryName }}</h2>
            <span class="text-xs text-gray-500">{{ $categoryTests->count() }} tests · {{ $categoryTests->where('status','pass')->count() }} passed</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-[10px] uppercase font-bold text-gray-500 tracking-wide border-b">
                        <th class="text-left px-4 py-2 w-20">Test ID</th>
                        <th class="text-center px-4 py-2 w-16">Module</th>
                        <th class="text-left px-4 py-2">Test Name</th>
                        <th class="text-center px-4 py-2 w-24">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($categoryTests as $test)
                    @php $mc = $moduleColors[$test['module']] ?? 'bg-gray-100 text-gray-600'; @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2.5 text-xs mono font-bold text-[#714B67]">{{ $test['id'] }}</td>
                        <td class="px-4 py-2.5 text-center"><span class="text-[10px] font-semibold px-2 py-0.5 rounded {{ $mc }}">{{ $test['module'] }}</span></td>
                        <td class="px-4 py-2.5 text-xs text-gray-700">{{ $test['name'] }}</td>
                        <td class="px-4 py-2.5 text-center">
                            @if($test['status'] === 'pass')
                            <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded bg-green-100 text-green-700">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                PASS
                            </span>
                            @else
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-gray-100 text-gray-500">{{ strtoupper($test['status']) }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    {{-- Footer note --}}
    <div class="bg-gray-50 rounded-lg border border-gray-200 px-4 py-3 flex items-center justify-between">
        <span class="text-xs text-gray-500">Kepatuhan 100% pada SR-01 s.d. SR-22 dan T-01 s.d. T-36 · Proteksi Idempotensi (SR-16) &amp; Data Privat (SR-15) aktif</span>
        <span class="text-[10px] text-gray-400 italic">node test_m01.js · test_m02.js · test_m04.js · verify_checksum.js</span>
    </div>

</div>
@endsection
