@extends('layouts.app')

@section('title', 'Goods Receipts')

@section('breadcrumb')
    <span class="mx-1">›</span>
    <span>Inventory</span>
    <span class="mx-1">›</span>
    <span class="font-medium text-gray-700">Receipts</span>
@endsection

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-800">Goods Receipts</h1>
            <p class="text-xs text-gray-400 mt-0.5">Penerimaan barang dari vendor · SR-06 3-way match · SR-17 sign-off</p>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="sticky-th text-left px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">GR#</th>
                    <th class="sticky-th text-left px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">PO#</th>
                    <th class="sticky-th text-left px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">Vendor</th>
                    <th class="sticky-th text-left px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">Lokasi</th>
                    <th class="sticky-th text-center px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                    <th class="sticky-th text-left px-4 py-2.5 font-semibold text-gray-500 uppercase tracking-wide">Validated</th>
                    <th class="sticky-th px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($receipts as $receipt)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-2.5 mono font-medium text-[#714B67]">{{ $receipt->id }}</td>
                    <td class="px-4 py-2.5 mono text-gray-600">{{ $receipt->purchase_order_id }}</td>
                    <td class="px-4 py-2.5 text-gray-700">{{ $receipt->purchaseOrder?->vendor_name ?? $receipt->vendor_id }}</td>
                    <td class="px-4 py-2.5 text-gray-500">{{ $receipt->destination_location }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                            {{ $receipt->status === 'done' ? 'bg-green-100 text-green-700' : ($receipt->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600') }}">
                            {{ strtoupper($receipt->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-gray-500">{{ $receipt->validated_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td class="px-4 py-2.5">
                        <a href="{{ route('inventory.receipts.show', $receipt->id) }}"
                           class="text-[#714B67] hover:underline text-xs">Lihat →</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">
                        Belum ada Goods Receipt. Buat dari PO Confirmed.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $receipts->links() }}</div>
</div>
@endsection
