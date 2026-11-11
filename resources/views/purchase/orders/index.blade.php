@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('breadcrumb')
    <span class="mx-1">›</span>
    <span>Purchase</span>
    <span class="mx-1">›</span>
    <span class="font-medium text-gray-700">Orders</span>
@endsection

@section('content')
<div class="space-y-5">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="page-title">Purchase Orders</h1>
            <p class="page-sub">RFQ → Konfirmasi → Penerimaan Barang · SR-21 price lock · SR-06 3-way match</p>
        </div>
        <a href="{{ route('purchase.orders.create') }}"
           class="btn-primary text-xs flex items-center gap-1.5">
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
    <div class="panel overflow-hidden">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left px-4 py-2.5 font-semibold text-gray-400 uppercase tracking-wide text-[10px]">PO#</th>
                    <th class="text-left px-4 py-2.5 font-semibold text-gray-400 uppercase tracking-wide text-[10px]">Vendor</th>
                    <th class="text-left px-4 py-2.5 font-semibold text-gray-400 uppercase tracking-wide text-[10px]">Tgl Order</th>
                    <th class="text-right px-4 py-2.5 font-semibold text-gray-400 uppercase tracking-wide text-[10px]">Net</th>
                    <th class="text-right px-4 py-2.5 font-semibold text-gray-400 uppercase tracking-wide text-[10px]">Gross</th>
                    <th class="text-center px-4 py-2.5 font-semibold text-gray-400 uppercase tracking-wide text-[10px]">Status</th>
                    <th class="px-4 py-2.5"></th>
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
