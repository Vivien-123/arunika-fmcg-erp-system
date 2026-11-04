@extends('layouts.app')
@section('title','Sales Orders')
@php
    $currentApp  = 'Sales';
    $sidebarMenu = [
        ['label'=>'Semua SO',    'url'=>route('sales.orders.index'),  'icon'=>'📋'],
        ['label'=>'Buat Quotation','url'=>route('sales.orders.create'),'icon'=>'➕'],
        ['label'=>'CRM Pipeline','url'=>route('crm.leads.index'),     'icon'=>'🎯'],
    ];
    $statusColors = [
        'draft'      => 'bg-gray-100 text-gray-600',
        'to_approve' => 'bg-yellow-100 text-yellow-700',
        'confirmed'  => 'bg-green-100 text-green-700',
        'done'       => 'bg-blue-100 text-blue-700',
        'cancelled'  => 'bg-red-100 text-red-600',
    ];
@endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-400">›</span>
    <a href="{{ route('sales.orders.index') }}" class="text-[#714B67] hover:underline">Sales</a>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700 font-medium">Orders</span>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-2">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">① Quotation / Sales Order</h1>
            <p class="text-xs text-gray-500 mt-0.5">F-05, F-06, F-10, F-22 · S-05 · T-09, T-10</p>
        </div>
        <a href="{{ route('sales.orders.create') }}"
           class="bg-[#714B67] text-white px-4 py-2 rounded text-sm hover:bg-[#5c3d56] transition font-medium">
            ➕ Buat Quotation
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded px-4 py-2 mb-3 text-sm">✅ {{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded px-4 py-2 mb-3 text-sm">⚠️ {{ session('warning') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded px-4 py-2 mb-3 text-sm">⛔ {{ session('error') }}</div>
    @endif

    {{-- Search / Filters --}}
    <form method="GET" class="bg-white border rounded-lg p-3 mb-4 flex flex-wrap gap-2 items-end">
        <div class="flex-1 min-w-[160px]">
            <label class="block text-xs text-gray-500 mb-0.5">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="SO Number / Pelanggan"
                   class="w-full border rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#714B67]">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-0.5">Status</label>
            <select name="status" class="border rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-[#714B67] focus:outline-none">
                <option value="">Semua</option>
                @foreach(['draft'=>'Draft','to_approve'=>'To Approve','confirmed'=>'Confirmed','done'=>'Done','cancelled'=>'Cancelled'] as $v => $l)
                <option value="{{ $v }}" {{ ($filters['status']??'')===$v ? 'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-0.5">Pelanggan</label>
            <select name="customer_id" class="border rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-[#714B67] focus:outline-none">
                <option value="">Semua</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}" {{ ($filters['customer_id']??'')===$c->id ? 'selected':'' }}>{{ $c->id }} — {{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="bg-[#714B67] text-white px-3 py-1.5 rounded text-sm hover:bg-[#5c3d56] transition">Terapkan</button>
        <a href="{{ route('sales.orders.index') }}" class="text-gray-500 text-sm px-3 py-1.5 hover:underline">Reset</a>
    </form>

    {{-- ④ List table --}}
    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs text-gray-500 uppercase tracking-wider border-b">
                <tr>
                    <th class="px-4 py-3 text-left">Nomor</th>
                    <th class="px-4 py-3 text-left">Pelanggan</th>
                    <th class="px-4 py-3 text-right">Net</th>
                    <th class="px-4 py-3 text-right">PPN</th>
                    <th class="px-4 py-3 text-right">Gross</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Tanggal</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-mono font-semibold text-[#714B67]">
                        <a href="{{ route('sales.orders.show', $order->id) }}" class="hover:underline">{{ $order->id }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ $order->customer_name }}</td>
                    <td class="px-4 py-3 text-right text-gray-600">Rp{{ number_format($order->untaxed_amount) }}</td>
                    <td class="px-4 py-3 text-right text-gray-400">Rp{{ number_format($order->tax_amount) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-800">Rp{{ number_format($order->gross_amount) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-xs px-2 py-1 rounded-full font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-500' }}">
                            {{ ucfirst(str_replace('_',' ',$order->status)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-400 text-xs">{{ $order->order_date }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('sales.orders.show', $order->id) }}"
                           class="text-[#714B67] text-xs hover:underline">Detail →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-10 text-center text-gray-400 italic">Tidak ada Sales Order.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($orders->hasPages())
        <div class="px-4 py-3 border-t">{{ $orders->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
