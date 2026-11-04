@extends('layouts.app')
@section('title', $invoice->document_number)
@php $currentApp = 'Accounting'; $sidebarMenu = [
    ['label'=>'Vendor Bills','url'=>route('accounting.bills.index'),'icon'=>'📄'],
]; @endphp

@section('content')
<div class="max-w-4xl mx-auto px-2">
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-xs px-4 py-2.5 rounded-lg mb-4">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-2.5 rounded-lg mb-4">❌ {{ $errors->first() }}</div>
    @endif

    <div class="flex items-start justify-between mb-5">
        <div>
            <p class="text-xs text-gray-400 mb-1"><a href="{{ route('accounting.bills.index') }}" class="hover:underline">Vendor Bills</a> › {{ $invoice->document_number }}</p>
            <h1 class="text-xl font-bold text-gray-800">{{ $invoice->document_number }}</h1>
            <p class="text-xs text-gray-500 mt-0.5">{{ $invoice->partner_name }} · PO: {{ $invoice->source_document_id ?? '-' }}</p>
        </div>
        <div class="flex gap-2 items-center">
            @php $colors = ['draft'=>'bg-yellow-100 text-yellow-700','posted'=>'bg-blue-100 text-blue-700','paid'=>'bg-green-100 text-green-700']; @endphp
            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $colors[$invoice->status] ?? 'bg-gray-100 text-gray-600' }}">
                {{ strtoupper($invoice->status) }}
            </span>
            @if($invoice->status === 'draft')
            <form method="POST" action="{{ route('accounting.bills.post', $invoice->id) }}">
                @csrf
                <button class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-1.5 rounded-lg transition">Post Bill</button>
            </form>
            @endif
            @if($invoice->status === 'posted')
            <a href="{{ route('accounting.payments.create', $invoice->id) }}" class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-4 py-1.5 rounded-lg transition">Bayar</a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3 shadow-sm">
            <p class="text-[10px] text-gray-400 uppercase">Net</p>
            <p class="text-base font-bold text-gray-800 mt-0.5">Rp{{ number_format($invoice->untaxed_amount,0,',','.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3 shadow-sm">
            <p class="text-[10px] text-gray-400 uppercase">PPN Masukan</p>
            <p class="text-base font-bold text-gray-800 mt-0.5">Rp{{ number_format($invoice->tax_amount,0,',','.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3 shadow-sm">
            <p class="text-[10px] text-gray-400 uppercase">Gross / Outstanding</p>
            <p class="text-base font-bold text-gray-800 mt-0.5">Rp{{ number_format($invoice->gross_amount,0,',','.') }}</p>
            @if($invoice->amount_outstanding > 0)
            <p class="text-[10px] text-red-500 font-semibold mt-0.5">Sisa: Rp{{ number_format($invoice->amount_outstanding,0,',','.') }}</p>
            @else
            <p class="text-[10px] text-green-600 font-semibold mt-0.5">LUNAS</p>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/80">
            <h3 class="font-semibold text-gray-700 text-sm">Baris Bill (3-Way Match)</h3>
        </div>
        <table class="w-full text-xs">
            <thead class="bg-gray-50"><tr>
                <th class="text-left px-4 py-2 text-gray-500">SKU</th>
                <th class="text-right px-4 py-2 text-gray-500">Qty</th>
                <th class="text-right px-4 py-2 text-gray-500">Accepted</th>
                <th class="text-right px-4 py-2 text-gray-500">Harga</th>
                <th class="text-right px-4 py-2 text-gray-500">Net</th>
                <th class="text-right px-4 py-2 text-gray-500">PPN</th>
                <th class="text-right px-4 py-2 text-gray-500">Gross</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($invoice->items as $item)
                <tr class="hover:bg-gray-50/60">
                    <td class="px-4 py-2 font-mono text-gray-600">{{ $item->variant_sku }}</td>
                    <td class="px-4 py-2 text-right">{{ $item->quantity }}</td>
                    <td class="px-4 py-2 text-right text-green-600 font-semibold">{{ $item->accepted_qty }}</td>
                    <td class="px-4 py-2 text-right">Rp{{ number_format($item->unit_price,0,',','.') }}</td>
                    <td class="px-4 py-2 text-right">Rp{{ number_format($item->net_amount,0,',','.') }}</td>
                    <td class="px-4 py-2 text-right text-gray-500">Rp{{ number_format($item->tax_amount,0,',','.') }}</td>
                    <td class="px-4 py-2 text-right font-semibold">Rp{{ number_format($item->gross_amount,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($auditLogs->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/80">
            <h3 class="font-semibold text-gray-700 text-sm">Log Audit</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($auditLogs->take(5) as $log)
            <div class="px-4 py-3 flex items-start gap-3">
                <span class="text-base">📝</span>
                <div class="flex-1">
                    <p class="text-xs font-semibold text-gray-700">{{ $log->action }} <span class="font-normal text-gray-400">oleh {{ $log->actor_user }}</span></p>
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ $log->description }}</p>
                </div>
                <span class="text-[10px] text-gray-400 whitespace-nowrap">{{ $log->acted_at->format('d/m H:i') }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
