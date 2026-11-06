@extends('layouts.app')

@section('title', 'Buat Delivery Order')

@section('breadcrumb')
    <span class="mx-1">›</span>
    <a href="{{ route('inventory.deliveries.index') }}" class="hover:text-[#714B67]">Deliveries</a>
    <span class="mx-1">›</span>
    <span class="font-medium text-gray-700">Buat</span>
@endsection

@section('content')
<div class="max-w-2xl space-y-4">
    <div>
        <h1 class="text-lg font-semibold text-gray-800">Buat Delivery Order</h1>
        <p class="text-xs text-gray-400 mt-0.5">Pilih SO Confirmed untuk membuat DO (FEFO picking)</p>
    </div>

    <form method="POST" action="{{ route('inventory.deliveries.store') }}" class="space-y-3">
        @csrf

        <div class="bg-white rounded-lg border border-gray-200 p-4 space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    Sales Order <span class="text-red-500">*</span>
                </label>
                @if($so)
                    <div class="bg-[#F7F3F6] border border-[#D8C4D1] rounded px-3 py-2 text-xs">
                        <strong class="mono text-[#714B67]">{{ $so->id }}</strong>
                        — {{ $so->customer_name }}
                        <span class="text-gray-500 ml-2">Gross: Rp {{ number_format($so->gross_amount) }}</span>
                    </div>
                    <input type="hidden" name="sales_order_id" value="{{ $so->id }}">
                @else
                    <select name="sales_order_id" required
                        class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                        <option value="">-- Pilih SO Confirmed --</option>
                        @foreach($confirmedSOs as $cso)
                        <option value="{{ $cso->id }}">{{ $cso->id }} — {{ $cso->customer_name }} (Gross: Rp {{ number_format($cso->gross_amount) }})</option>
                        @endforeach
                    </select>
                    @if($confirmedSOs->isEmpty())
                    <p class="text-xs text-amber-600 mt-1">⚠️ Tidak ada SO berstatus Confirmed. Konfirmasi SO terlebih dahulu.</p>
                    @endif
                @endif
            </div>
        </div>

        @if($so)
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-4 py-2 border-b border-gray-100 bg-gray-50">
                <p class="text-xs font-semibold text-gray-600">Lines SO yang akan dikirim</p>
            </div>
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left px-3 py-2 font-semibold text-gray-500">SKU</th>
                        <th class="text-center px-3 py-2 font-semibold text-gray-500">Qty SO</th>
                        <th class="text-center px-3 py-2 font-semibold text-gray-500">Sudah Kirim</th>
                        <th class="text-center px-3 py-2 font-semibold text-gray-500">Sisa</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($so->lines as $line)
                    <tr>
                        <td class="px-3 py-2 mono text-[#714B67]">{{ $line->variant_sku }}</td>
                        <td class="px-3 py-2 text-center tnum">{{ $line->quantity }}</td>
                        <td class="px-3 py-2 text-center tnum text-green-700">{{ $line->delivered_qty }}</td>
                        <td class="px-3 py-2 text-center tnum font-bold {{ ($line->quantity - $line->delivered_qty) > 0 ? 'text-blue-700' : 'text-gray-400' }}">
                            {{ $line->quantity - $line->delivered_qty }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="bg-blue-50 border border-blue-200 rounded p-2 text-xs text-blue-700">
            ℹ️ SR-07/SR-09: FEFO lot picking otomatis. SR-04: Stok negatif diblokir. SR-17: Validasi wajib employee Warehouse Aktif.
        </div>

        <div class="flex gap-3">
            <button type="submit"
                class="bg-[#714B67] text-white text-xs font-medium px-4 py-2 rounded hover:bg-[#5E3D56] transition">
                Buat Delivery Order
            </button>
            <a href="{{ route('inventory.deliveries.index') }}"
               class="text-xs text-gray-500 px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
