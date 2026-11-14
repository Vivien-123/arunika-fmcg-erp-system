@extends('layouts.app')

@section('title', 'Receipt ' . $receipt->id)

@section('breadcrumb')
    <span class="mx-1">›</span>
    <a href="{{ route('inventory.receipts.index') }}" class="hover:text-[#714B67]">Receipts</a>
    <span class="mx-1">›</span>
    <span class="font-medium text-gray-700">{{ $receipt->id }}</span>
@endsection

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <span class="mono text-base font-bold text-[#714B67]">{{ $receipt->id }}</span>
                    @php
                        $badge = match($receipt->status) {
                            'draft'     => 'bg-gray-100 text-gray-600',
                            'done'      => 'bg-green-100 text-green-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                            default     => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                        {{ strtoupper($receipt->status) }}
                    </span>
                    @if($receipt->is_posted)
                    <span class="text-[10px] text-green-600 bg-green-50 border border-green-200 px-2 py-0.5 rounded">
                        ✓ SR-16 Posted
                    </span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 space-x-3">
                    <span>PO: <a href="{{ route('purchase.orders.show', $receipt->purchase_order_id) }}" class="mono text-[#714B67] hover:underline">{{ $receipt->purchase_order_id }}</a></span>
                    <span>Vendor: <strong>{{ $receipt->purchaseOrder?->vendor_name ?? $receipt->vendor_id }}</strong></span>
                    <span>Lokasi Tujuan: <strong>{{ $receipt->destination_location }}</strong></span>
                </div>
            </div>

            {{-- Pipeline --}}
            <div class="flex items-center gap-1 text-xs">
                @foreach(['draft'=>'Draft','done'=>'Done'] as $s => $label)
                    <span class="px-2 py-0.5 rounded {{ $receipt->status === $s ? 'bg-[#714B67] text-white' : ($receipt->status === 'done' && $s === 'draft' ? 'bg-[#714B67] text-white' : 'bg-gray-100 text-gray-400') }}">{{ $label }}</span>
                    @if(!$loop->last) <span class="text-gray-300">→</span> @endif
                @endforeach
            </div>
        </div>

        @if($receipt->validated_at)
        <div class="mt-3 pt-3 border-t border-gray-100 text-xs text-gray-500">
            Divalidasi oleh <strong>{{ $receipt->validator_user }}</strong>
            ({{ $receipt->validatorEmployee?->name ?? $receipt->validator_employee_id }})
            pada {{ $receipt->validated_at->format('d M Y H:i') }}
        </div>
        @endif
    </div>

    {{-- Receipt Lines --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Receipt Lines</h2>
        </div>

        @if($receipt->status !== 'done' && auth()->user()->canEdit())
        <form method="POST" action="{{ route('inventory.receipts.validate', $receipt->id) }}" id="validateForm">
            @csrf
        @endif

        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-4 py-2 font-semibold text-gray-500">SKU</th>
                    <th class="text-left px-4 py-2 font-semibold text-gray-500">Produk</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">Ordered</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">Received</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">Accepted <span class="text-green-600">✓</span></th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">Rejected <span class="text-red-500">✗</span></th>
                    <th class="text-left px-4 py-2 font-semibold text-gray-500">Lot / Exp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($receipt->lines as $line)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 mono text-[#714B67]">{{ $line->variant_sku }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ $line->product_name }}</td>
                    <td class="px-4 py-2 text-center tnum">{{ $line->ordered_qty }}</td>
                    <td class="px-4 py-2 text-center">
                        @if($receipt->status !== 'done' && auth()->user()->canEdit())
                        <input type="number" name="lines[{{ $line->id }}][received_qty]"
                            min="0" max="{{ $line->ordered_qty }}" value="{{ $line->received_qty ?: $line->ordered_qty }}"
                            class="w-14 border border-gray-300 rounded px-1 py-0.5 text-center focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                        @else
                        <span class="tnum">{{ $line->received_qty ?: $line->ordered_qty }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-center">
                        @if($receipt->status !== 'done' && auth()->user()->canEdit())
                        <input type="number" name="lines[{{ $line->id }}][accepted_qty]"
                            min="0" max="{{ $line->ordered_qty }}" value="{{ $line->accepted_qty ?: $line->ordered_qty }}"
                            class="w-14 border border-gray-300 rounded px-1 py-0.5 text-center focus:outline-none focus:ring-1 focus:ring-[#714B67] border-green-300">
                        @else
                        <span class="tnum font-medium text-green-700">{{ $line->accepted_qty ?: $line->ordered_qty }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-center tnum {{ $line->rejected_qty > 0 ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                        {{ $line->rejected_qty ?: '—' }}
                    </td>
                    <td class="px-4 py-2">
                        @if($receipt->status !== 'done' && auth()->user()->canEdit())
                        <div class="flex items-center gap-1">
                            <input type="text" name="lines[{{ $line->id }}][lot_id_override]"
                                placeholder="LOT-xxx"
                                class="w-20 text-xs border border-gray-300 rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                            <input type="date" name="lines[{{ $line->id }}][expiry_date_override]"
                                class="text-xs border border-gray-300 rounded px-1 py-0.5 focus:outline-none focus:ring-1 focus:ring-[#714B67]">
                        </div>
                        @else
                        <span class="mono text-xs text-gray-500">{{ $line->lot_id ?? '—' }}</span>
                        @if($line->expiry_date) <span class="text-gray-400 text-[10px] ml-1">{{ $line->expiry_date->format('d M Y') }}</span> @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($receipt->status !== 'done')
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
                    <p class="text-xs text-red-500 mt-1">⚠️ Tidak ada employee Warehouse Aktif.</p>
                    @endif
                </div>
                <div class="mt-4">
                    <button type="submit" form="validateForm"
                        class="bg-green-600 text-white text-xs font-medium px-4 py-2 rounded hover:bg-green-700 transition">
                        ✓ Validasi & Set Done
                    </button>
                </div>
            </div>
            <p class="text-[10px] text-gray-400">SR-06: Hanya accepted_qty masuk stok · SR-17: Wajib Employee Aktif D-WH · SR-16: Idempotent</p>
        </div>
        </form>
        @endif
    </div>

    @if($receipt->status === 'done')
    <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-xs text-green-800">
        ✅ Receipt Done. Stok bertambah sesuai <strong>accepted_qty</strong> (SR-06).
        Journal Dr 1300 Persediaan / Cr 1350 Stock Interim sudah diposting (SR-03, confirmed answer §9).
    </div>

    {{-- ── GR → Vendor Bill (SR-06, 3-way match, M2-W3-01) ── --}}
    @php
        use App\Models\PurchaseOrder;
        $po           = PurchaseOrder::with('lines')->find($receipt->purchase_order_id);
        $vendor       = \App\Models\Vendor::find($receipt->vendor_id);
        $canBill      = false;
        $billLines    = [];
        if ($po && $vendor) {
            foreach ($receipt->lines as $rl) {
                $poLine      = $po->lines->firstWhere('id', $rl->purchase_order_line_id);
                $alreadyBilled = $poLine ? (int)$poLine->billed_qty : 0;
                $billableQty   = (int)$rl->accepted_qty - $alreadyBilled;
                if ($billableQty > 0) {
                    $canBill = true;
                    $billLines[] = [
                        'po_line_id'    => $poLine?->id,
                        'variant_sku'   => $rl->variant_sku,
                        'product_id'    => $rl->product_id,
                        'product_name'  => $rl->product_name,
                        'unit'          => $rl->unit,
                        'unit_price'    => $poLine ? (int)$poLine->unit_price : 0,
                        'accepted_qty'  => (int)$rl->accepted_qty,
                        'already_billed'=> $alreadyBilled,
                        'billable_qty'  => $billableQty,
                    ];
                }
            }
        }
    @endphp

    @if($canBill)
    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 space-y-3">
        <div>
            <h3 class="text-sm font-semibold text-amber-800">Buat Vendor Bill — 3-Way Match (SR-06)</h3>
            <p class="text-xs text-amber-600 mt-0.5">Bill berdasarkan accepted_qty − already_billed. Rejected qty tidak ditagih.</p>
        </div>

        <table class="w-full text-xs border border-amber-200 rounded overflow-hidden">
            <thead>
                <tr class="bg-amber-100">
                    <th class="text-left px-3 py-1.5 font-semibold text-amber-700">SKU</th>
                    <th class="text-center px-3 py-1.5 font-semibold text-amber-700">Accepted</th>
                    <th class="text-center px-3 py-1.5 font-semibold text-amber-700">Already Billed</th>
                    <th class="text-center px-3 py-1.5 font-semibold text-amber-700">Billable</th>
                    <th class="text-right px-3 py-1.5 font-semibold text-amber-700">Unit Price</th>
                    <th class="text-right px-3 py-1.5 font-semibold text-amber-700">Net</th>
                </tr>
            </thead>
            <tbody>
                @foreach($billLines as $bl)
                <tr class="border-t border-amber-100">
                    <td class="px-3 py-1.5 mono text-[#714B67]">{{ $bl['variant_sku'] }}</td>
                    <td class="px-3 py-1.5 text-center tnum">{{ $bl['accepted_qty'] }}</td>
                    <td class="px-3 py-1.5 text-center tnum text-gray-400">{{ $bl['already_billed'] }}</td>
                    <td class="px-3 py-1.5 text-center tnum font-semibold text-green-700">{{ $bl['billable_qty'] }}</td>
                    <td class="px-3 py-1.5 text-right tnum">Rp{{ number_format($bl['unit_price']) }}</td>
                    <td class="px-3 py-1.5 text-right tnum font-medium">Rp{{ number_format($bl['billable_qty'] * $bl['unit_price']) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <form method="POST" action="{{ route('accounting.bills.store') }}">
            @csrf
            <input type="hidden" name="partner_id"    value="{{ $vendor->id }}">
            <input type="hidden" name="partner_name"  value="{{ $vendor->name }}">
            <input type="hidden" name="source_po"     value="{{ $receipt->purchase_order_id }}">
            <input type="hidden" name="source_gr"     value="{{ $receipt->id }}">
            <input type="hidden" name="payment_terms" value="{{ $po?->payment_terms ?? 'NET14' }}">
            @foreach($billLines as $i => $bl)
            <input type="hidden" name="lines[{{ $i }}][product_id]"     value="{{ $bl['product_id'] }}">
            <input type="hidden" name="lines[{{ $i }}][variant_sku]"    value="{{ $bl['variant_sku'] }}">
            <input type="hidden" name="lines[{{ $i }}][product_name]"   value="{{ $bl['product_name'] }}">
            <input type="hidden" name="lines[{{ $i }}][unit]"           value="{{ $bl['unit'] }}">
            <input type="hidden" name="lines[{{ $i }}][unit_price]"     value="{{ $bl['unit_price'] }}">
            <input type="hidden" name="lines[{{ $i }}][accepted_qty]"   value="{{ $bl['accepted_qty'] }}">
            <input type="hidden" name="lines[{{ $i }}][already_billed]" value="{{ $bl['already_billed'] }}">
            <input type="hidden" name="lines[{{ $i }}][po_line_id]"     value="{{ $bl['po_line_id'] }}">
            @endforeach

            <button type="submit"
                class="bg-amber-700 text-white text-xs font-semibold px-5 py-2 rounded hover:bg-amber-800 transition">
                📄 Buat Vendor Bill
            </button>
        </form>
    </div>
    @else
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-xs text-gray-500">
        ℹ️ Tidak ada qty yang bisa ditagih — semua accepted_qty sudah di-bill.
    </div>
    @endif
    @endif
</div>
@endsection
