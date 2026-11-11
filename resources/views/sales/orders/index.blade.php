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
<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="page-title">Sales Orders</h1>
            <p class="page-sub">F-05 Quotation → Confirmed · F-06 Diskon · F-10 Credit limit · SR-12 Price lock</p>
        </div>
        <a href="{{ route('sales.orders.create') }}" class="btn-primary text-xs flex items-center gap-1.5">
            + Buat Quotation
        </a>
    </div>

    @if(session('success'))
        {{-- Flash handled by layout --}}
    @endif

    {{-- Search / Filters --}}
    <form method="GET" class="panel p-3 mb-5 flex flex-wrap gap-2 items-end">
        <div class="flex-1 min-w-[180px]">
            <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Cari</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="SO Number / Pelanggan"
                   class="form-input w-full text-xs">
        </div>
        <div>
            <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Status</label>
            <select name="status" class="form-input text-xs">
                <option value="">Semua Status</option>
                @foreach(['draft'=>'Draft','to_approve'=>'To Approve','confirmed'=>'Confirmed','done'=>'Done','cancelled'=>'Cancelled'] as $v => $l)
                <option value="{{ $v }}" {{ ($filters['status']??'')===$v ? 'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-1">Pelanggan</label>
            <select name="customer_id" class="form-input text-xs">
                <option value="">Semua Pelanggan</option>
                @foreach($customers as $c)
                <option value="{{ $c->id }}" {{ ($filters['customer_id']??'')===$c->id ? 'selected':'' }}>{{ $c->id }} — {{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn-primary text-xs">Terapkan</button>
        <a href="{{ route('sales.orders.index') }}" class="btn-secondary text-xs">Reset</a>
    </form>

    {{-- ④ List table --}}
    <div class="panel overflow-hidden">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Nomor</th>
                    <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Pelanggan</th>
                    <th class="px-4 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Net</th>
                    <th class="px-4 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide">PPN</th>
                    <th class="px-4 py-2.5 text-right text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Gross</th>
                    <th class="px-4 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-2.5 text-center text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Tanggal</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($orders as $order)
                <tr class="tr-hover" onclick="window.location='{{ route('sales.orders.show', $order->id) }}'">
                    <td class="px-4 py-3 font-mono font-semibold text-[#714B67]">{{ $order->id }}</td>
                    <td class="px-4 py-3 text-gray-700 font-medium">{{ $order->customer_name }}</td>
                    <td class="px-4 py-3 text-right text-gray-500 tnum">Rp{{ number_format($order->untaxed_amount) }}</td>
                    <td class="px-4 py-3 text-right text-gray-400 tnum">Rp{{ number_format($order->tax_amount) }}</td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-800 tnum">Rp{{ number_format($order->gross_amount) }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="badge {{ match($order->status) {
                            'draft'      => 'badge-gray',
                            'to_approve' => 'badge-yellow',
                            'confirmed'  => 'badge-green',
                            'done'       => 'badge-blue',
                            'cancelled'  => 'badge-red',
                            default      => 'badge-gray',
                        } }}">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-400">{{ $order->order_date ?? '—' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('sales.orders.show', $order->id) }}"
                           class="text-[#714B67] text-xs hover:underline" onclick="event.stopPropagation()">Detail →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                        <p class="font-medium">Belum ada Sales Order</p>
                        <a href="{{ route('sales.orders.create') }}" class="text-[#714B67] text-xs hover:underline mt-1 inline-block">Buat Quotation pertama →</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($orders->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">{{ $orders->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
