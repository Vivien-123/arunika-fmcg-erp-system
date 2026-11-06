@extends('layouts.app')

@section('title', 'Delivery ' . $delivery->id)

@section('breadcrumb')
    <span class="mx-1">›</span>
    <a href="{{ route('inventory.deliveries.index') }}" class="hover:text-[#714B67]">Deliveries</a>
    <span class="mx-1">›</span>
    <span class="font-medium text-gray-700">{{ $delivery->id }}</span>
@endsection

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <span class="mono text-base font-bold text-[#714B67]">{{ $delivery->id }}</span>
                    @php
                        $badge = match($delivery->status) {
                            'draft'     => 'bg-gray-100 text-gray-600',
                            'ready'     => 'bg-blue-100 text-blue-700',
                            'done'      => 'bg-green-100 text-green-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                            default     => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                        {{ strtoupper($delivery->status) }}
                    </span>
                    @if($delivery->is_posted)
                    <span class="text-[10px] text-green-600 bg-green-50 border border-green-200 px-2 py-0.5 rounded">
                        ✓ SR-16 Posted
                    </span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 space-x-3">
                    <span>SO: <a href="{{ route('sales.orders.show', $delivery->sales_order_id) }}" class="mono text-[#714B67] hover:underline">{{ $delivery->sales_order_id }}</a></span>
                    <span>Customer: {{ $delivery->customer_id }}</span>
                    <span>Sumber: <strong>{{ $delivery->source_location }}</strong></span>
                </div>
            </div>

            {{-- Pipeline --}}
            <div class="flex items-center gap-1 text-xs">
                @foreach(['draft'=>'Draft','ready'=>'Ready','done'=>'Done'] as $s => $label)
                    @php
                        $stages = ['draft','ready','done'];
                        $current = array_search($delivery->status, $stages);
                        $thisIdx = array_search($s, $stages);
                    @endphp
                    <span class="px-2 py-0.5 rounded {{ $thisIdx <= $current ? 'bg-[#714B67] text-white' : 'bg-gray-100 text-gray-400' }}">{{ $label }}</span>
                    @if(!$loop->last) <span class="text-gray-300">→</span> @endif
                @endforeach
            </div>
        </div>

        @if($delivery->validated_at)
        <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
            Divalidasi oleh <strong>{{ $delivery->validator_user }}</strong>
            (Employee: {{ $delivery->validatorEmployee?->name ?? $delivery->validator_employee_id }})
            pada {{ $delivery->validated_at->format('d M Y H:i') }}
        </div>
        @endif
    </div>

    {{-- Delivery Lines --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Delivery Lines</h2>
        </div>

        @if($delivery->status !== 'done')
        <form method="POST" action="{{ route('inventory.deliveries.validate', $delivery->id) }}" id="validateForm">
            @csrf
        @endif

        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-4 py-2 font-semibold text-gray-500">SKU</th>
                    <th class="text-left px-4 py-2 font-semibold text-gray-500">Produk</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">Demand</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">Done Qty</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">Backorder</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">Lot (FEFO)</th>
                    <th class="text-right px-4 py-2 font-semibold text-gray-500">Available</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($delivery->lines as $line)
                @php $si = $stockInfo[$line->variant_sku] ?? null; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 mono text-[#714B67]">{{ $line->variant_sku }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ $line->product_name }}</td>
                    <td class="px-4 py-2 text-center tnum">{{ $line->demand_qty }}</td>
                    <td class="px-4 py-2 text-center">
                        @if($delivery->status !== 'done')
                        <input type="number" name="done_qties[{{ $line->id }}]"
                            min="0" max="{{ $si ? $si['available'] : $line->demand_qty }}"
                            value="{{ $line->done_qty ?: $line->demand_qty }}"
                            class="w-16 border border-gray-300 rounded px-1 py-0.5 text-center focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                        @else
                        <span class="tnum font-medium text-green-700">{{ $line->done_qty }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-center tnum text-amber-600">{{ $line->backorder_qty ?: '—' }}</td>
                    <td class="px-4 py-2 text-center mono text-xs text-gray-500">
                        {{ $line->lot_id ?? '—' }}
                    </td>
                    <td class="px-4 py-2 text-right">
                        @if($si)
                        <span class="{{ $si['available'] > 0 ? 'text-green-700' : 'text-red-600' }} tnum font-medium">
                            {{ $si['available'] }}
                        </span>
                        <span class="text-gray-400"> / {{ $si['on_hand'] }}</span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($delivery->status !== 'done')
        {{-- Validator section inside the form --}}
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50 space-y-3">
            <h3 class="text-xs font-semibold text-gray-600">Sign-off Validator (SR-17/SR-18)</h3>
            <div class="flex items-center gap-3 flex-wrap">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Employee Warehouse Aktif <span class="text-red-500">*</span></label>
                    <select name="validator_employee_id" required
                        class="text-xs border border-gray-300 rounded px-2 py-1.5 focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                        <option value="">-- Pilih Validator --</option>
                        @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->id }} — {{ $emp->name }} ({{ $emp->job_title }})</option>
                        @endforeach
                    </select>
                    @if($employees->isEmpty())
                    <p class="text-xs text-red-500 mt-1">⚠️ Tidak ada employee Warehouse Aktif untuk sign-off.</p>
                    @endif
                </div>
                <div class="mt-4">
                    <button type="submit" form="validateForm"
                        class="bg-green-600 text-white text-xs font-medium px-4 py-2 rounded hover:bg-green-700 transition">
                        ✓ Validasi & Set Done
                    </button>
                </div>
            </div>
            <p class="text-[10px] text-gray-400">SR-04: Stok negatif diblokir · SR-07: FEFO lot auto-picked · SR-16: Idempotent · SR-17: Hanya Employee Aktif D-WH</p>
        </div>
        </form>
        @endif
    </div>

    {{-- Info box for done --}}
    @if($delivery->status === 'done')
    <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-xs text-green-800">
        ✅ Delivery Done. Stok sudah dikurangi. COGS journal (Dr 5100 / Cr 1300) sudah diposting (SR-03).
        SO lines <code class="mono">delivered_qty</code> sudah diupdate.
    </div>

    {{-- ── DO → Customer Invoice (SR-02, M2-W3-01) ── --}}
    @php
        use App\Models\SalesOrder;
        $so         = SalesOrder::with('lines.product')->find($delivery->sales_order_id);
        $customer   = \App\Models\Customer::find($delivery->customer_id);
        $canInvoice = false;
        $invoiceLines = [];
        if ($so && $customer) {
            foreach ($delivery->lines as $dLine) {
                $soLine = $so->lines->firstWhere('id', $dLine->sales_order_line_id);
                if (!$soLine) continue;
                $billableQty = (int)$soLine->delivered_qty - (int)$soLine->invoiced_qty;
                if ($billableQty > 0) {
                    $canInvoice = true;
                    $invoiceLines[] = [
                        'variant_sku'   => $soLine->variant_sku,
                        'product_name'  => $soLine->product_name,
                        'unit_price'    => $soLine->unit_price,
                        'delivered_qty' => (int)$soLine->delivered_qty,
                        'invoiced_qty'  => (int)$soLine->invoiced_qty,
                        'billable_qty'  => $billableQty,
                    ];
                }
            }
        }
    @endphp

    @if($canInvoice)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-3">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-blue-800">Buat Customer Invoice (SR-02)</h3>
                <p class="text-xs text-blue-600 mt-0.5">Penagihan berdasarkan delivered_qty − invoiced_qty. Hanya qty Done yang ditagih.</p>
            </div>
        </div>

        {{-- Preview billable qty --}}
        <table class="w-full text-xs border border-blue-200 rounded overflow-hidden">
            <thead>
                <tr class="bg-blue-100">
                    <th class="text-left px-3 py-1.5 font-semibold text-blue-700">SKU</th>
                    <th class="text-center px-3 py-1.5 font-semibold text-blue-700">Delivered</th>
                    <th class="text-center px-3 py-1.5 font-semibold text-blue-700">Already Invoiced</th>
                    <th class="text-center px-3 py-1.5 font-semibold text-blue-700 text-green-700">Billable</th>
                    <th class="text-right px-3 py-1.5 font-semibold text-blue-700">Unit Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoiceLines as $il)
                <tr class="border-t border-blue-100">
                    <td class="px-3 py-1.5 mono text-[#714B67]">{{ $il['variant_sku'] }}</td>
                    <td class="px-3 py-1.5 text-center tnum">{{ $il['delivered_qty'] }}</td>
                    <td class="px-3 py-1.5 text-center tnum text-gray-400">{{ $il['invoiced_qty'] }}</td>
                    <td class="px-3 py-1.5 text-center tnum font-semibold text-green-700">{{ $il['billable_qty'] }}</td>
                    <td class="px-3 py-1.5 text-right tnum">Rp{{ number_format($il['unit_price']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <form method="POST" action="{{ route('accounting.invoices.store') }}">
            @csrf
            <input type="hidden" name="partner_id"    value="{{ $customer->id }}">
            <input type="hidden" name="partner_name"  value="{{ $customer->name }}">
            <input type="hidden" name="source_so"     value="{{ $delivery->sales_order_id }}">
            <input type="hidden" name="source_do"     value="{{ $delivery->id }}">
            <input type="hidden" name="payment_terms" value="{{ $so->payment_terms ?? 'NET14' }}">
            @foreach($invoiceLines as $i => $il)
            @php
                $soLine = $so->lines->firstWhere('variant_sku', $il['variant_sku']);
            @endphp
            <input type="hidden" name="lines[{{ $i }}][product_id]"    value="{{ $soLine?->product_id ?? '' }}">
            <input type="hidden" name="lines[{{ $i }}][variant_sku]"   value="{{ $il['variant_sku'] }}">
            <input type="hidden" name="lines[{{ $i }}][product_name]"  value="{{ $il['product_name'] }}">
            <input type="hidden" name="lines[{{ $i }}][unit]"          value="{{ $soLine?->unit ?? 'unit' }}">
            <input type="hidden" name="lines[{{ $i }}][unit_price]"    value="{{ $il['unit_price'] }}">
            <input type="hidden" name="lines[{{ $i }}][delivered_qty]" value="{{ $il['delivered_qty'] }}">
            <input type="hidden" name="lines[{{ $i }}][invoiced_qty]"  value="{{ $il['invoiced_qty'] }}">
            <input type="hidden" name="lines[{{ $i }}][so_line_id]"    value="{{ $soLine?->id ?? '' }}">
            @endforeach

            <button type="submit"
                class="bg-[#714B67] text-white text-xs font-semibold px-5 py-2 rounded hover:bg-[#5a3a52] transition">
                📄 Buat Customer Invoice
            </button>
        </form>
    </div>
    @else
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-xs text-gray-500">
        ℹ️ Tidak ada qty yang bisa ditagih — semua delivered_qty sudah di-invoice.
    </div>
    @endif
    @endif
</div>
@endsection
