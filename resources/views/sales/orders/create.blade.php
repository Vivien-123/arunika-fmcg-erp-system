@extends('layouts.app')
@section('title','Buat Quotation')
@php
    $currentApp  = 'Sales';
    $sidebarMenu = [
        ['label'=>'Semua SO', 'url'=>route('sales.orders.index'), 'icon'=>'📋'],
    ];
@endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-400">›</span>
    <a href="{{ route('sales.orders.index') }}" class="text-[#714B67] hover:underline">Sales Orders</a>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700 font-medium">Buat Quotation</span>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-2">

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded px-4 py-2 mb-3 text-sm">⛔ {{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('sales.orders.store') }}" id="so-form">
    @csrf

    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">

        {{-- ① Title --}}
        <div class="px-5 py-4 border-b bg-gray-50">
            <span class="text-xs text-gray-400">F-05, F-06, F-10, F-22 · S-05</span>
            <h1 class="text-lg font-bold text-gray-800 mt-0.5">① Quotation Baru</h1>
            <p class="text-xs text-gray-500">② Status: <span class="font-semibold">Draft</span></p>
        </div>

        {{-- ③ Header fields --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 px-5 py-4 border-b">
            <div>
                <label class="block text-xs text-gray-500 mb-0.5">Customer <span class="text-red-500">*</span></label>
                <select name="customer_id" id="customer_id" required
                        class="w-full border rounded px-2 py-2 text-sm focus:ring-2 focus:ring-[#714B67] focus:outline-none"
                        onchange="loadCreditExposure(this.value)">
                    <option value="">— Pilih Pelanggan —</option>
                    @foreach($customers as $c)
                    <option value="{{ $c->id }}" data-limit="{{ $c->credit_limit }}">
                        {{ $c->id }} — {{ $c->name }} (Limit: Rp{{ number_format($c->credit_limit) }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-0.5">Payment Terms</label>
                <select name="payment_terms" class="w-full border rounded px-2 py-2 text-sm focus:ring-2 focus:ring-[#714B67] focus:outline-none">
                    <option value="">— Ikuti Master Pelanggan —</option>
                    <option value="Tunai">Tunai</option>
                    <option value="14 hari">14 Hari</option>
                    <option value="30 hari">30 Hari</option>
                </select>
            </div>
        </div>

        {{-- Credit exposure panel --}}
        <div id="credit-panel" class="hidden px-5 py-3 border-b bg-amber-50 border-amber-200">
            <div class="flex flex-wrap gap-4 text-xs">
                <div><span class="text-gray-500">Credit Limit:</span> <span id="cr-limit" class="font-bold text-gray-700">—</span></div>
                <div><span class="text-gray-500">Open AR:</span> <span id="cr-ar" class="font-bold">—</span></div>
                <div><span class="text-gray-500">Exposure Saat Ini:</span> <span id="cr-exposure" class="font-bold">—</span></div>
                <div><span class="text-gray-500">Tersedia:</span> <span id="cr-available" class="font-bold text-green-700">—</span></div>
            </div>
            <p id="credit-warning" class="hidden text-red-600 font-semibold text-xs mt-1">
                ⚠️ Melebihi credit limit — SO akan ditolak saat konfirmasi (SR-11)
            </p>
        </div>

        {{-- ④ Line items --}}
        <div class="px-5 py-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-semibold text-gray-700">④ Baris Produk</h3>
                <button type="button" onclick="addLine()"
                        class="text-xs bg-[#714B67] text-white px-3 py-1 rounded hover:bg-[#5c3d56] transition">
                    + Tambah Baris
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="lines-table">
                    <thead class="text-xs text-gray-500 border-b">
                        <tr>
                            <th class="text-left py-2 pr-2">SKU</th>
                            <th class="text-right py-2 pr-2">Qty</th>
                            <th class="text-right py-2 pr-2">Harga (Rp)</th>
                            <th class="text-right py-2 pr-2">Diskon (%)</th>
                            <th class="text-right py-2 pr-2">Net</th>
                            <th class="text-right py-2 pr-2">PPN 11%</th>
                            <th class="text-right py-2">Gross</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody id="lines-body">
                        {{-- Dynamic rows added by JS --}}
                    </tbody>
                    <tfoot class="border-t bg-gray-50 text-sm font-semibold">
                        <tr>
                            <td colspan="4" class="py-2 px-2 text-right text-gray-500">Total:</td>
                            <td class="py-2 text-right pr-2" id="total-net">Rp0</td>
                            <td class="py-2 text-right pr-2" id="total-tax">Rp0</td>
                            <td class="py-2 text-right" id="total-gross">Rp0</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Submit --}}
        <div class="px-5 py-4 border-t bg-gray-50 flex gap-2">
            <button type="submit" class="bg-[#714B67] text-white px-5 py-2 rounded text-sm hover:bg-[#5c3d56] transition font-medium">
                💾 Simpan Quotation
            </button>
            <a href="{{ route('sales.orders.index') }}" class="text-gray-500 text-sm px-4 py-2 hover:underline">Batal</a>
        </div>
    </div>
    </form>
</div>

<script>
const products = @json($products->map(fn($p) => [
    'variant_sku' => $p->variant_sku,
    'id'          => $p->id,
    'name'        => $p->name,
    'unit'        => $p->unit,
    'sales_price' => $p->sales_price,
]));

let lineIdx = 0;

function addLine() {
    const tbody = document.getElementById('lines-body');
    const i = lineIdx++;
    const options = products.map(p =>
        `<option value="${p.variant_sku}" data-price="${p.sales_price}" data-unit="${p.unit}"
                 data-name="${p.name}" data-id="${p.id}">${p.variant_sku} — ${p.name}</option>`
    ).join('');

    const row = document.createElement('tr');
    row.id = `line-${i}`;
    row.className = 'border-b align-middle';
    row.innerHTML = `
        <td class="pr-2 py-1">
            <select name="lines[${i}][variant_sku]" onchange="onSkuChange(${i})" required
                    class="border rounded px-1 py-1 text-xs w-48 focus:ring-1 focus:ring-[#714B67] focus:outline-none">
                <option value="">— Pilih SKU —</option>${options}
            </select>
            <input type="hidden" name="lines[${i}][product_id]" id="pid-${i}">
            <input type="hidden" name="lines[${i}][product_name]" id="pname-${i}">
            <input type="hidden" name="lines[${i}][unit]" id="unit-${i}">
        </td>
        <td class="pr-2 py-1"><input type="number" name="lines[${i}][quantity]" id="qty-${i}" min="1" value="1" required
               onchange="recalcLine(${i})" class="border rounded px-1 py-1 text-xs w-16 text-right focus:ring-1 focus:ring-[#714B67] focus:outline-none"></td>
        <td class="pr-2 py-1"><input type="number" name="lines[${i}][unit_price]" id="price-${i}" min="0" value="0" required
               onchange="recalcLine(${i})" class="border rounded px-1 py-1 text-xs w-24 text-right focus:ring-1 focus:ring-[#714B67] focus:outline-none"></td>
        <td class="pr-2 py-1"><input type="number" name="lines[${i}][discount_percent]" id="disc-${i}" min="0" max="100" step="0.01" value="0"
               onchange="recalcLine(${i})" class="border rounded px-1 py-1 text-xs w-16 text-right focus:ring-1 focus:ring-[#714B67] focus:outline-none">
               <span id="disc-warn-${i}" class="hidden text-xs text-yellow-600 ml-1">▲ Butuh Approval</span>
        </td>
        <td id="net-${i}"  class="pr-2 py-1 text-right text-xs text-gray-700">Rp0</td>
        <td id="tax-${i}"  class="pr-2 py-1 text-right text-xs text-gray-500">Rp0</td>
        <td id="grs-${i}"  class="py-1 text-right text-xs font-semibold text-gray-800">Rp0</td>
        <td class="py-1 pl-1"><button type="button" onclick="removeLine(${i})" class="text-red-400 hover:text-red-600 text-xs">✕</button></td>`;
    tbody.appendChild(row);
}

function onSkuChange(i) {
    const sel = document.querySelector(`select[name="lines[${i}][variant_sku]"]`);
    const opt = sel.options[sel.selectedIndex];
    document.getElementById(`pid-${i}`).value   = opt.dataset.id   || '';
    document.getElementById(`pname-${i}`).value = opt.dataset.name || '';
    document.getElementById(`unit-${i}`).value  = opt.dataset.unit || '';
    document.getElementById(`price-${i}`).value = opt.dataset.price || 0;
    recalcLine(i);
}

function recalcLine(i) {
    const qty   = parseInt(document.getElementById(`qty-${i}`)?.value)   || 0;
    const price = parseInt(document.getElementById(`price-${i}`)?.value) || 0;
    const disc  = parseFloat(document.getElementById(`disc-${i}`)?.value) || 0;
    const net   = Math.round(qty * price * (1 - disc / 100));
    const tax   = Math.floor(net * 0.11 + 0.5);
    const gross = net + tax;

    document.getElementById(`net-${i}`).textContent = 'Rp' + net.toLocaleString('id');
    document.getElementById(`tax-${i}`).textContent = 'Rp' + tax.toLocaleString('id');
    document.getElementById(`grs-${i}`).textContent = 'Rp' + gross.toLocaleString('id');

    const warn = document.getElementById(`disc-warn-${i}`);
    if (warn) warn.classList.toggle('hidden', disc <= 10);

    recalcTotals();
}

function recalcTotals() {
    let totalNet = 0, totalTax = 0, totalGross = 0;
    document.querySelectorAll('[id^="net-"]').forEach(el => {
        totalNet += parseInt(el.textContent.replace(/[^\d]/g,'')) || 0;
    });
    document.querySelectorAll('[id^="tax-"]').forEach(el => {
        totalTax += parseInt(el.textContent.replace(/[^\d]/g,'')) || 0;
    });
    document.querySelectorAll('[id^="grs-"]').forEach(el => {
        totalGross += parseInt(el.textContent.replace(/[^\d]/g,'')) || 0;
    });
    document.getElementById('total-net').textContent   = 'Rp' + totalNet.toLocaleString('id');
    document.getElementById('total-tax').textContent   = 'Rp' + totalTax.toLocaleString('id');
    document.getElementById('total-gross').textContent = 'Rp' + totalGross.toLocaleString('id');
}

function removeLine(i) {
    const row = document.getElementById(`line-${i}`);
    if (row) { row.remove(); recalcTotals(); }
}

function loadCreditExposure(customerId) {
    if (!customerId) { document.getElementById('credit-panel').classList.add('hidden'); return; }
    fetch(`/sales/api/credit/${customerId}`)
        .then(r => r.json())
        .then(d => {
            document.getElementById('credit-panel').classList.remove('hidden');
            document.getElementById('cr-limit').textContent    = 'Rp' + (d.credit_limit||0).toLocaleString('id');
            document.getElementById('cr-ar').textContent       = 'Rp' + (d.open_ar||0).toLocaleString('id');
            document.getElementById('cr-exposure').textContent = 'Rp' + (d.exposure||0).toLocaleString('id');
            document.getElementById('cr-available').textContent= 'Rp' + (d.available||0).toLocaleString('id');
            const warn = document.getElementById('credit-warning');
            warn.classList.toggle('hidden', d.available > 0);
        });
}

// Add one blank line on load
addLine();
</script>
@endsection
