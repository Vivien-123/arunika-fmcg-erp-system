@extends('layouts.app')

@section('title', 'Buat Goods Receipt')

@section('breadcrumb')
    <span class="mx-1">›</span>
    <a href="{{ route('purchase.orders.index') }}" class="hover:text-[#714B67]">Purchase Orders</a>
    <span class="mx-1">›</span>
    <a href="{{ route('purchase.orders.show', $order->id) }}" class="hover:text-[#714B67]">{{ $order->id }}</a>
    <span class="mx-1">›</span>
    <span class="font-medium text-gray-700">Buat Receipt</span>
@endsection

@section('content')
<div class="max-w-3xl space-y-4">
    <div>
        <h1 class="text-lg font-semibold text-gray-800">Buat Goods Receipt</h1>
        <p class="text-xs text-gray-400 mt-0.5">dari PO {{ $order->id }} — {{ $order->vendor_name }}</p>
    </div>

    {{-- PO Summary --}}
    <div class="bg-[#F7F3F6] rounded-lg border border-[#D8C4D1] p-3">
        <div class="flex items-center gap-6 text-xs">
            <span><span class="text-gray-400">PO#:</span> <strong class="mono">{{ $order->id }}</strong></span>
            <span><span class="text-gray-400">Vendor:</span> <strong>{{ $order->vendor_name }}</strong></span>
            <span><span class="text-gray-400">Status:</span> <strong class="text-blue-700">CONFIRMED</strong></span>
        </div>
    </div>

    {{-- Lines to receive --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Items dari PO</h2>
        </div>
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50">
                    <th class="text-left px-4 py-2 font-semibold text-gray-500">SKU / Produk</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">Qty PO</th>
                    <th class="text-right px-4 py-2 font-semibold text-gray-500">Harga/Unit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($order->lines as $line)
                <tr>
                    <td class="px-4 py-2">
                        <div class="mono text-[#714B67]">{{ $line->variant_sku }}</div>
                        <div class="text-gray-500 mt-0.5">{{ $line->product_name }}</div>
                    </td>
                    <td class="px-4 py-2 text-center tnum">{{ $line->quantity }} {{ $line->unit }}</td>
                    <td class="px-4 py-2 text-right tnum">Rp {{ number_format($line->unit_price) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Create Receipt Form --}}
    <form method="POST" action="{{ route('inventory.receipts.store') }}">
        @csrf
        <input type="hidden" name="purchase_order_id" value="{{ $order->id }}">

        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <p class="text-sm text-gray-600 mb-3">
                Membuat Goods Receipt <strong>GR00xxx</strong> dalam status <span class="text-gray-500 font-medium">Draft</span>.
                Setelah dibuat, isi qty yang diterima dan accepted, lalu validasi dengan employee Warehouse aktif (SR-17).
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded p-2 text-xs text-blue-700">
                ℹ️ SR-06: Hanya <strong>accepted_qty</strong> yang menambah stok. Qty yang ditolak tidak dihitung.
            </div>
        </div>

        <div class="flex gap-3 mt-3">
            <button type="submit"
                class="bg-[#018B91] text-white text-xs font-medium px-4 py-2 rounded hover:bg-[#017278] transition">
                Buat Receipt Draft
            </button>
            <a href="{{ route('purchase.orders.show', $order->id) }}"
               class="text-xs text-gray-500 px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
