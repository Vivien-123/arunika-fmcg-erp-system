@extends('layouts.app')

@section('title', 'Delivery Orders')

@section('breadcrumb')
    <span class="mx-1">›</span>
    <span>Inventory</span>
    <span class="mx-1">›</span>
    <span class="font-medium text-gray-700">Deliveries</span>
@endsection

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-800">Delivery Orders</h1>
            <p class="text-xs text-gray-400 mt-0.5">FEFO picking · SR-04 anti-negative · SR-17 sign-off</p>
        </div>
        <a href="{{ route('inventory.deliveries.create') }}"
           class="inline-flex items-center gap-1.5 bg-[#714B67] text-white text-xs font-medium px-3 py-1.5 rounded hover:bg-[#5E3D56] transition">
            + Buat Delivery
        </a>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="sticky-th text-left px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">DO#</th>
                    <th class="sticky-th text-left px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">SO#</th>
                    <th class="sticky-th text-left px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">Customer</th>
                    <th class="sticky-th text-left px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">Sumber</th>
                    <th class="sticky-th text-center px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="sticky-th text-left px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">Validator</th>
                    <th class="sticky-th px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($deliveries as $delivery)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-2.5 mono font-medium text-[#714B67]">{{ $delivery->id }}</td>
                    <td class="px-4 py-2.5 mono text-gray-600">{{ $delivery->sales_order_id }}</td>
                    <td class="px-4 py-2.5 text-gray-700">{{ $delivery->customer_id }}</td>
                    <td class="px-4 py-2.5 text-gray-500">{{ $delivery->source_location }}</td>
                    <td class="px-4 py-2.5 text-center">
                        @php
                            $badge = match($delivery->status) {
                                'draft'     => 'bg-gray-100 text-gray-600',
                                'ready'     => 'bg-blue-100 text-blue-700',
                                'done'      => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                                default     => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $badge }}">
                            {{ strtoupper($delivery->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-gray-500">{{ $delivery->validator_user ?? '—' }}</td>
                    <td class="px-4 py-2.5">
                        <a href="{{ route('inventory.deliveries.show', $delivery->id) }}"
                           class="text-[#714B67] hover:underline text-xs">Lihat →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">
                        Belum ada Delivery Order.
                        <a href="{{ route('inventory.deliveries.create') }}" class="text-[#714B67] hover:underline ml-1">Buat pertama</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $deliveries->links() }}</div>
</div>
@endsection
