@extends('layouts.app')

@section('title', 'PO ' . $order->id)

@section('breadcrumb')
    <span class="mx-1">›</span>
    <a href="{{ route('purchase.orders.index') }}" class="hover:text-[#714B67]">Purchase Orders</a>
    <span class="mx-1">›</span>
    <span class="font-medium text-gray-700">{{ $order->id }}</span>
@endsection

@section('smart_buttons')
    <span class="text-xs text-gray-400">Smart Buttons:</span>
    <a href="{{ route('inventory.receipts.index') }}"
       class="inline-flex items-center gap-1 px-3 py-1 border border-gray-200 rounded-lg bg-white hover:bg-gray-50 transition text-xs text-gray-700">
        🚚 Receipts <span class="ml-1 bg-[#714B67] text-white text-[10px] px-1.5 py-0.5 rounded-full">{{ $receiptCount }}</span>
    </a>
    <span class="text-xs text-gray-500 px-2">Accepted Qty: <strong>{{ $acceptedTotal }}</strong></span>
@endsection

@section('content')
<div class="space-y-4">

    {{-- Status bar --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="space-y-1">
                <div class="flex items-center gap-3">
                    <span class="mono text-base font-bold text-[#714B67]">{{ $order->id }}</span>
                    @php
                        $badge = match($order->status) {
                            'rfq'       => 'bg-yellow-100 text-yellow-800',
                            'confirmed' => 'bg-blue-100 text-blue-800',
                            'done'      => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-700',
                            default     => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                        {{ strtoupper($order->status) }}
                    </span>
                    @if($order->is_locked)
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] bg-orange-50 text-orange-700 border border-orange-200">
                        🔒 SR-21 Locked
                    </span>
                    @endif
                </div>
                <p class="text-xs text-gray-500">{{ $order->vendor_name }} · {{ $order->payment_terms }}</p>
            </div>

            {{-- Status pipeline --}}
            <div class="flex items-center gap-1 text-xs">
                @foreach(['rfq'=>'RFQ','confirmed'=>'Confirmed','done'=>'Done'] as $s => $label)
                    @php
                        $statuses = ['rfq', 'confirmed', 'done'];
                        $current  = array_search($order->status, $statuses);
                        $this_idx = array_search($s, $statuses);
                        $active   = $this_idx <= $current;
                    @endphp
                    <div class="flex items-center gap-1">
                        <span class="px-2 py-0.5 rounded {{ $active ? 'bg-[#714B67] text-white' : 'bg-gray-100 text-gray-400' }}">
                            {{ $label }}
                        </span>
                        @if(!$loop->last) <span class="text-gray-300">→</span> @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Actions --}}
        @if(auth()->user()->canEdit())
        @if($order->status === 'rfq')
        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100">
            <form method="POST" action="{{ route('purchase.orders.confirm', $order->id) }}">
                @csrf
                <button type="submit"
                    class="bg-blue-600 text-white text-xs font-medium px-3 py-1.5 rounded hover:bg-blue-700 transition">
                    ✓ Konfirmasi PO (SR-21)
                </button>
            </form>
            <form method="POST" action="{{ route('purchase.orders.cancel', $order->id) }}"
                  onsubmit="return confirm('Batalkan RFQ {{ $order->id }}?')">
                @csrf
                <button type="submit" class="text-red-600 text-xs border border-red-200 px-3 py-1.5 rounded hover:bg-red-50 transition">
                    Batalkan
                </button>
            </form>
        </div>
        @endif

        @if($order->status === 'confirmed')
        <div class="flex items-center gap-2 mt-3 pt-3 border-t border-gray-100">
            <a href="{{ route('purchase.orders.receipt.create', $order->id) }}"
               class="bg-[#018B91] text-white text-xs font-medium px-3 py-1.5 rounded hover:bg-[#017278] transition">
                + Buat Receipt (GR)
            </a>
        </div>
        @endif
        @endif
    </div>

    {{-- PO Info --}}
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white rounded-lg border border-gray-200 p-3">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide mb-1.5">Vendor</p>
            <p class="text-sm font-semibold text-gray-800">{{ $order->vendor_name }}</p>
            <p class="text-xs text-gray-500">{{ $order->vendor->city ?? '' }} · {{ $order->vendor->category ?? '' }}</p>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-3">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide mb-1.5">Tanggal</p>
            <p class="text-sm text-gray-700">Order: <strong>{{ $order->order_date?->format('d M Y') ?? '-' }}</strong></p>
            @if($order->confirmed_at)
            <p class="text-xs text-gray-500">Dikonfirmasi: {{ $order->confirmed_at->format('d M Y H:i') }} oleh {{ $order->confirmed_by }}</p>
            @endif
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-3">
            <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide mb-1.5">Total</p>
            <p class="text-xs text-gray-500">Net: <span class="tnum">Rp {{ number_format($order->net_amount) }}</span></p>
            <p class="text-xs text-gray-500">Tax: <span class="tnum">Rp {{ number_format($order->tax_amount) }}</span></p>
            <p class="text-sm font-bold text-gray-800">Gross: <span class="tnum">Rp {{ number_format($order->gross_amount) }}</span></p>
        </div>
    </div>

    {{-- Order Lines --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Order Lines</h2>
        </div>
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-4 py-2 font-semibold text-gray-500">SKU</th>
                    <th class="text-left px-4 py-2 font-semibold text-gray-500">Produk</th>
                    <th class="text-left px-4 py-2 font-semibold text-gray-500">Unit</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">Qty</th>
                    <th class="text-right px-4 py-2 font-semibold text-gray-500">Harga/Unit</th>
                    <th class="text-right px-4 py-2 font-semibold text-gray-500">Net</th>
                    <th class="text-right px-4 py-2 font-semibold text-gray-500">Accepted</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">Lock</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($order->lines as $line)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 mono text-[#714B67]">{{ $line->variant_sku }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ $line->product_name }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $line->unit }}</td>
                    <td class="px-4 py-2 text-center tnum">{{ $line->quantity }}</td>
                    <td class="px-4 py-2 text-right tnum">Rp {{ number_format($line->unit_price) }}</td>
                    <td class="px-4 py-2 text-right tnum font-medium">Rp {{ number_format($line->net_amount) }}</td>
                    <td class="px-4 py-2 text-right tnum text-green-700">{{ $line->accepted_qty }}</td>
                    <td class="px-4 py-2 text-center">
                        @if($line->is_locked)
                        <span class="text-orange-500 text-xs">🔒</span>
                        @else
                        <span class="text-gray-300 text-xs">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 border-t border-gray-200">
                <tr>
                    <td colspan="5" class="px-4 py-2 text-right text-xs font-semibold text-gray-600">Total Net:</td>
                    <td class="px-4 py-2 text-right tnum font-bold text-gray-800">Rp {{ number_format($order->net_amount) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Receipts --}}
    @if($receipts->count() > 0)
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">Goods Receipts ({{ $receipts->count() }})</h2>
        </div>
        <table class="w-full text-xs">
            <thead>
                <tr class="bg-gray-50">
                    <th class="text-left px-4 py-2 font-semibold text-gray-500">GR#</th>
                    <th class="text-left px-4 py-2 font-semibold text-gray-500">Lokasi</th>
                    <th class="text-center px-4 py-2 font-semibold text-gray-500">Status</th>
                    <th class="text-right px-4 py-2 font-semibold text-gray-500">Accepted Qty</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($receipts as $receipt)
                <tr>
                    <td class="px-4 py-2 mono text-[#714B67]">{{ $receipt->id }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $receipt->destination_location }}</td>
                    <td class="px-4 py-2 text-center">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold
                            {{ $receipt->status === 'done' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ strtoupper($receipt->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right tnum">{{ $receipt->lines->sum('accepted_qty') }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('inventory.receipts.show', $receipt->id) }}" class="text-[#714B67] hover:underline text-xs">Lihat</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
