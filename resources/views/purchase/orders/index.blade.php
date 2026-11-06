@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('breadcrumb')
    <span class="mx-1">›</span>
    <span>Purchase</span>
    <span class="mx-1">›</span>
    <span class="font-medium text-gray-700">Orders</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-800">Purchase Orders</h1>
            <p class="text-xs text-gray-400 mt-0.5">RFQ, Confirmed PO & Goods Receipt tracking</p>
        </div>
        <a href="{{ route('purchase.orders.create') }}"
           class="inline-flex items-center gap-1.5 bg-[#714B67] text-white text-xs font-medium px-3 py-1.5 rounded hover:bg-[#5E3D56] transition">
            + Buat RFQ
        </a>
    </div>

    {{-- Status filter --}}
    <div class="flex items-center gap-2 flex-wrap">
        @foreach([''=>'Semua','rfq'=>'RFQ','confirmed'=>'Confirmed','done'=>'Done','cancelled'=>'Cancelled'] as $val => $label)
        <a href="{{ route('purchase.orders.index', $val ? ['status'=>$val] : []) }}"
           class="px-3 py-1 text-xs rounded-full border transition
               {{ $status === $val ? 'bg-[#714B67] text-white border-[#714B67]' : 'bg-white text-gray-600 border-gray-300 hover:border-[#714B67]' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="sticky-th text-left px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">PO#</th>
                    <th class="sticky-th text-left px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">Vendor</th>
                    <th class="sticky-th text-left px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">Tgl Order</th>
                    <th class="sticky-th text-right px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">Net</th>
                    <th class="sticky-th text-right px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">Gross</th>
                    <th class="sticky-th text-center px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="sticky-th px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-2.5 mono font-medium text-[#714B67]">{{ $order->id }}</td>
                    <td class="px-4 py-2.5 text-gray-700">{{ $order->vendor_name }}</td>
                    <td class="px-4 py-2.5 text-gray-500">{{ $order->order_date?->format('d M Y') ?? '-' }}</td>
                    <td class="px-4 py-2.5 text-right tnum text-gray-700">Rp {{ number_format($order->net_amount) }}</td>
                    <td class="px-4 py-2.5 text-right tnum font-semibold text-gray-800">Rp {{ number_format($order->gross_amount) }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @php
                            $badge = match($order->status) {
                                'rfq'       => 'bg-yellow-100 text-yellow-800',
                                'confirmed' => 'bg-blue-100 text-blue-800',
                                'done'      => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-700',
                                default     => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $badge }}">
                            {{ strtoupper($order->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5">
                        <a href="{{ route('purchase.orders.show', $order->id) }}"
                           class="text-[#714B67] hover:underline text-xs">Lihat →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">
                        Belum ada Purchase Order.
                        <a href="{{ route('purchase.orders.create') }}" class="text-[#714B67] hover:underline ml-1">Buat RFQ pertama</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div>{{ $orders->withQueryString()->links() }}</div>
</div>
@endsection
