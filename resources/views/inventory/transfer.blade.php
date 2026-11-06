@extends('layouts.app')

@section('title', 'Internal Transfer')

@section('breadcrumb')
    <span class="mx-1">›</span>
    <span>Inventory</span>
    <span class="mx-1">›</span>
    <span class="font-medium text-gray-700">Internal Transfer</span>
@endsection

@section('content')
<div class="max-w-2xl space-y-4">
    <div>
        <h1 class="text-lg font-semibold text-gray-800">Internal Transfer</h1>
        <p class="text-xs text-gray-400 mt-0.5">SR-14: WH-JKT ↔ HUB-BDG · Company total tidak berubah · Tidak ada jurnal AR/AP/PPN</p>
    </div>

    <form method="POST" action="{{ route('inventory.transfer.store') }}" class="space-y-3">
        @csrf

        <div class="bg-white rounded-lg border border-gray-200 p-4 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Dari Lokasi <span class="text-red-500">*</span></label>
                    <select name="from_location" required
                        class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                        <option value="WH-JKT">WH-JKT — Gudang Utama Jakarta</option>
                        <option value="HUB-BDG">HUB-BDG — Hub Distribusi Bandung</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Ke Lokasi <span class="text-red-500">*</span></label>
                    <select name="to_location" required
                        class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                        <option value="HUB-BDG">HUB-BDG — Hub Distribusi Bandung</option>
                        <option value="WH-JKT">WH-JKT — Gudang Utama Jakarta</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Produk / SKU <span class="text-red-500">*</span></label>
                    <select name="variant_sku" required id="skuSelect" onchange="updateStock()"
                        class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                        <option value="">-- Pilih SKU --</option>
                        @foreach($stocks as $s)
                        <option value="{{ $s->variant_sku }}"
                            data-whjkt="{{ $s->wh_jkt_on_hand }}"
                            data-hubbdg="{{ $s->hub_bdg_on_hand }}"
                            data-reserved="{{ $s->reserved }}"
                            data-total="{{ $s->company_total }}">
                            {{ $s->variant_sku }} — {{ $s->product->name ?? '' }} (WH-JKT: {{ $s->wh_jkt_on_hand }} / HUB-BDG: {{ $s->hub_bdg_on_hand }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Qty <span class="text-red-500">*</span></label>
                    <input type="number" name="qty" min="1" required
                        class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                </div>
            </div>

            {{-- Stock info --}}
            <div id="stockInfo" class="hidden bg-gray-50 rounded border border-gray-200 p-3 text-xs space-y-1">
                <p class="font-semibold text-gray-600">Stok saat ini:</p>
                <p>WH-JKT: <strong id="whJktQty" class="tnum text-blue-700">-</strong> unit &nbsp;|&nbsp; HUB-BDG: <strong id="hubBdgQty" class="tnum text-blue-700">-</strong> unit</p>
                <p>Reserved: <strong id="reservedQty" class="tnum text-orange-600">-</strong> &nbsp;|&nbsp; Company Total: <strong id="totalQty" class="tnum">-</strong></p>
                <p class="text-[10px] text-gray-400">SR-22: Available WH-JKT = WH-JKT On Hand − Reserved</p>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded p-2 text-xs text-blue-700">
            ℹ️ SR-14: Nilai stok (standard_value) tidak berubah karena company total tetap sama. Tidak ada jurnal akuntansi.
            SR-22: Stok negatif diblokir.
        </div>

        <div class="flex gap-3">
            <button type="submit"
                class="bg-[#714B67] text-white text-xs font-medium px-4 py-2 rounded hover:bg-[#5E3D56] transition">
                Transfer Stok
            </button>
            <a href="{{ route('inventory.stock') }}"
               class="text-xs text-gray-500 px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 transition">
                Batal
            </a>
        </div>
    </form>

    {{-- Stock overview table --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Ringkasan Stok per Lokasi</h2>
        </div>
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50">
                    <th class="text-left px-4 py-2 font-semibold text-gray-500">SKU</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">WH-JKT</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">HUB-BDG</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">Reserved</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($stocks as $s)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 mono text-[#714B67]">{{ $s->variant_sku }}</td>
                    <td class="px-4 py-2 text-center tnum">{{ $s->wh_jkt_on_hand }}</td>
                    <td class="px-4 py-2 text-center tnum">{{ $s->hub_bdg_on_hand }}</td>
                    <td class="px-4 py-2 text-center tnum {{ $s->reserved > 0 ? 'text-orange-600' : 'text-gray-400' }}">{{ $s->reserved }}</td>
                    <td class="px-4 py-2 text-center tnum font-medium">{{ $s->company_total }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
function updateStock() {
    const sel = document.getElementById('skuSelect');
    const opt = sel.options[sel.selectedIndex];
    if (!opt.value) {
        document.getElementById('stockInfo').classList.add('hidden');
        return;
    }
    document.getElementById('whJktQty').textContent   = opt.dataset.whjkt;
    document.getElementById('hubBdgQty').textContent  = opt.dataset.hubbdg;
    document.getElementById('reservedQty').textContent = opt.dataset.reserved;
    document.getElementById('totalQty').textContent   = opt.dataset.total;
    document.getElementById('stockInfo').classList.remove('hidden');
}
</script>
@endsection
