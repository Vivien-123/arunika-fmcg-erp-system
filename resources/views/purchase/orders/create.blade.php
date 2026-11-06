@extends('layouts.app')

@section('title', 'Buat RFQ')

@section('breadcrumb')
    <span class="mx-1">›</span>
    <a href="{{ route('purchase.orders.index') }}" class="hover:text-[#714B67]">Purchase Orders</a>
    <span class="mx-1">›</span>
    <span class="font-medium text-gray-700">Buat RFQ</span>
@endsection

@section('content')
<div class="max-w-4xl space-y-4">
    <div>
        <h1 class="text-lg font-semibold text-gray-800">Buat RFQ</h1>
        <p class="text-xs text-gray-400 mt-0.5">Request for Quotation — harga dikunci saat konfirmasi (SR-21)</p>
    </div>

    <form method="POST" action="{{ route('purchase.orders.store') }}" id="rfqForm">
        @csrf

        {{-- Vendor --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4 space-y-3">
            <h2 class="text-sm font-semibold text-gray-700">Informasi Vendor</h2>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Vendor <span class="text-red-500">*</span></label>
                    <select name="vendor_id" id="vendorSelect"
                        class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#714B67]"
                        required onchange="updatePrices()">
                        <option value="">-- Pilih Vendor --</option>
                        @foreach($vendors as $v)
                        <option value="{{ $v->id }}" {{ old('vendor_id')==$v->id?'selected':'' }}>{{ $v->id }} — {{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tgl Order</label>
                    <input type="date" name="order_date" value="{{ date('Y-m-d') }}"
                        class="w-full text-xs border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                </div>
            </div>
        </div>

        {{-- Lines --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4 space-y-3 mt-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-700">Order Lines</h2>
                <button type="button" onclick="addLine()"
                    class="text-xs text-[#714B67] hover:underline font-medium">+ Tambah Produk</button>
            </div>

            <table class="w-full text-xs" id="linesTable">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="text-left px-2 py-1.5 font-semibold text-gray-500">Produk / SKU</th>
                        <th class="text-center px-2 py-1.5 font-semibold text-gray-500 w-20">Qty</th>
                        <th class="text-right px-2 py-1.5 font-semibold text-gray-500 w-28">Harga/Unit (Rp)</th>
                        <th class="text-right px-2 py-1.5 font-semibold text-gray-500 w-28">Subtotal</th>
                        <th class="w-8"></th>
                    </tr>
                </thead>
                <tbody id="linesBody">
                    {{-- JS rows added here --}}
                </tbody>
            </table>

            <p class="text-[10px] text-gray-400">* Harga otomatis dari vendor pricelist. SR-21: harga dikunci saat PO dikonfirmasi.</p>
        </div>

        <div class="flex gap-3 mt-3">
            <button type="submit" class="bg-[#714B67] text-white text-xs font-medium px-4 py-2 rounded hover:bg-[#5E3D56] transition">
                Simpan RFQ
            </button>
            <a href="{{ route('purchase.orders.index') }}" class="text-xs text-gray-500 px-4 py-2 border border-gray-300 rounded hover:bg-gray-50 transition">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
const pricelist = @json($pricelist);
const products  = @json($products->keyBy('variant_sku'));
let lineCount   = 0;

function addLine() {
    const vendorId = document.getElementById('vendorSelect').value;
    lineCount++;
    const idx = lineCount;
    const body = document.getElementById('linesBody');

    const options = Object.values(products).map(p => {
        const price = (pricelist[vendorId] && pricelist[vendorId].find(pl => pl.variant_sku === p.variant_sku))
            ? pricelist[vendorId].find(pl => pl.variant_sku === p.variant_sku).unit_price : p.cost;
        return `<option value="${p.variant_sku}" data-price="${price}">${p.variant_sku} — ${p.name} (${p.unit})</option>`;
    }).join('');

    const firstProduct = Object.values(products)[0];
    const vendorPl = pricelist[vendorId] || [];
    const defaultPrice = vendorPl.length > 0 && vendorPl[0].unit_price ? vendorPl[0].unit_price : firstProduct?.cost ?? 0;

    const tr = document.createElement('tr');
    tr.id = `line_${idx}`;
    tr.innerHTML = `
        <td class="px-2 py-1">
            <select name="lines[${idx}][variant_sku]" onchange="onSkuChange(this, ${idx})"
                class="w-full text-xs border border-gray-300 rounded px-1 py-1 focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                <option value="">-- Pilih SKU --</option>
                ${options}
            </select>
        </td>
        <td class="px-2 py-1">
            <input type="number" name="lines[${idx}][quantity]" min="1" value="1"
                oninput="calcRow(${idx})"
                class="w-full text-xs border border-gray-300 rounded px-1 py-1 text-center focus:outline-none focus:ring-1 focus:ring-[#714B67]">
        </td>
        <td class="px-2 py-1">
            <input type="number" name="lines[${idx}][unit_price]" id="price_${idx}" min="0" value="0"
                oninput="calcRow(${idx})"
                class="w-full text-xs border border-gray-300 rounded px-1 py-1 text-right focus:outline-none focus:ring-1 focus:ring-[#714B67]">
        </td>
        <td class="px-2 py-1 text-right tnum text-gray-700" id="subtotal_${idx}">Rp 0</td>
        <td class="px-2 py-1 text-center">
            <button type="button" onclick="removeLine(${idx})" class="text-red-400 hover:text-red-600 text-xs">✕</button>
        </td>
    `;
    body.appendChild(tr);
}

function onSkuChange(sel, idx) {
    const opt = sel.options[sel.selectedIndex];
    const price = opt.dataset.price || 0;
    document.getElementById(`price_${idx}`).value = price;
    calcRow(idx);
}

function calcRow(idx) {
    const qty   = parseInt(document.querySelector(`input[name="lines[${idx}][quantity]"]`).value) || 0;
    const price = parseInt(document.querySelector(`input[name="lines[${idx}][unit_price]"]`).value) || 0;
    document.getElementById(`subtotal_${idx}`).textContent = 'Rp ' + (qty * price).toLocaleString('id-ID');
}

function removeLine(idx) {
    const row = document.getElementById(`line_${idx}`);
    if (row) row.remove();
}

function updatePrices() {
    // Re-init existing rows when vendor changes
}

// Start with one empty line
addLine();
</script>
@endsection
