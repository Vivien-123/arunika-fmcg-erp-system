@extends('layouts.app')
@section('title', $invoice->document_number)
@php $currentApp = 'Accounting'; $sidebarMenu = [
    ['label'=>'Customer Invoices','url'=>route('accounting.invoices.index'),'icon'=>'🧾'],
    ['label'=>'Vendor Bills',     'url'=>route('accounting.bills.index'),   'icon'=>'📄'],
    ['label'=>'General Ledger',   'url'=>route('accounting.ledger.index'),  'icon'=>'📊'],
]; @endphp

@section('content')
<div class="max-w-4xl mx-auto px-2">
    {{-- Flash --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-xs px-4 py-2.5 rounded-lg mb-4">✅ {{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-2.5 rounded-lg mb-4">❌ {{ $errors->first() }}</div>
    @endif

    {{-- Header --}}
    <div class="flex items-start justify-between mb-5">
        <div>
            <p class="text-xs text-gray-400 mb-1"><a href="{{ route('accounting.invoices.index') }}" class="hover:underline">Customer Invoices</a> › {{ $invoice->document_number }}</p>
            <h1 class="text-xl font-bold text-gray-800">{{ $invoice->document_number }}</h1>
            <p class="text-xs text-gray-500 mt-0.5">{{ $invoice->partner_name }} · {{ $invoice->invoice_date?->format('d/m/Y') }}</p>
        </div>
        <div class="flex gap-2 items-center">
            @php $colors = ['draft'=>'bg-yellow-100 text-yellow-700','posted'=>'bg-blue-100 text-blue-700','paid'=>'bg-green-100 text-green-700']; @endphp
            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $colors[$invoice->status] ?? 'bg-gray-100 text-gray-600' }}">
                {{ strtoupper($invoice->status) }}
            </span>
            @if($invoice->status === 'draft')
            <form method="POST" action="{{ route('accounting.invoices.post', $invoice->id) }}">
                @csrf
                <button class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-1.5 rounded-lg transition">Post Invoice</button>
            </form>
            @endif
            @if($invoice->status === 'posted')
            <a href="{{ route('accounting.payments.create', $invoice->id) }}" class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-4 py-1.5 rounded-lg transition">Bayar</a>
            <a href="{{ route('accounting.credit-notes.create', $invoice->id) }}" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-4 py-1.5 rounded-lg transition">Credit Note</a>
            @endif
        </div>
    </div>

    {{-- Totals --}}
    <div class="grid grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3 shadow-sm">
            <p class="text-[10px] text-gray-400 uppercase tracking-wide">Net (DPP)</p>
            <p class="text-base font-bold text-gray-800 mt-0.5">Rp{{ number_format($invoice->untaxed_amount,0,',','.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3 shadow-sm">
            <p class="text-[10px] text-gray-400 uppercase tracking-wide">PPN 11%</p>
            <p class="text-base font-bold text-gray-800 mt-0.5">Rp{{ number_format($invoice->tax_amount,0,',','.') }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 px-4 py-3 shadow-sm">
            <p class="text-[10px] text-gray-400 uppercase tracking-wide">Gross / Outstanding</p>
            <p class="text-base font-bold text-gray-800 mt-0.5">Rp{{ number_format($invoice->gross_amount,0,',','.') }}</p>
            @if($invoice->amount_outstanding > 0)
            <p class="text-[10px] text-red-500 font-semibold mt-0.5">Sisa: Rp{{ number_format($invoice->amount_outstanding,0,',','.') }}</p>
            @else
            <p class="text-[10px] text-green-600 font-semibold mt-0.5">LUNAS</p>
            @endif
        </div>
    </div>

    {{-- Line items --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/80">
            <h3 class="font-semibold text-gray-700 text-sm">Baris Invoice</h3>
        </div>
        <table class="w-full text-xs">
            <thead class="bg-gray-50"><tr>
                <th class="text-left px-4 py-2 text-gray-500">SKU</th>
                <th class="text-left px-4 py-2 text-gray-500">Produk</th>
                <th class="text-right px-4 py-2 text-gray-500">Qty</th>
                <th class="text-right px-4 py-2 text-gray-500">Harga Satuan</th>
                <th class="text-right px-4 py-2 text-gray-500">Net</th>
                <th class="text-right px-4 py-2 text-gray-500">PPN</th>
                <th class="text-right px-4 py-2 text-gray-500">Gross</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($invoice->items as $item)
                <tr class="hover:bg-gray-50/60">
                    <td class="px-4 py-2 font-mono text-gray-600">{{ $item->variant_sku }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ $item->product_name }}</td>
                    <td class="px-4 py-2 text-right">{{ $item->quantity }} {{ $item->unit }}</td>
                    <td class="px-4 py-2 text-right">Rp{{ number_format($item->unit_price,0,',','.') }}</td>
                    <td class="px-4 py-2 text-right">Rp{{ number_format($item->net_amount,0,',','.') }}</td>
                    <td class="px-4 py-2 text-right text-gray-500">Rp{{ number_format($item->tax_amount,0,',','.') }}</td>
                    <td class="px-4 py-2 text-right font-semibold">Rp{{ number_format($item->gross_amount,0,',','.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Journal Entries --}}
    @if($invoice->journalEntries->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/80">
            <h3 class="font-semibold text-gray-700 text-sm">Journal Entries (F-14)</h3>
        </div>
        <table class="w-full text-xs">
            <thead class="bg-gray-50"><tr>
                <th class="text-left px-4 py-2 text-gray-500">Referensi</th>
                <th class="text-left px-4 py-2 text-gray-500">Debit</th>
                <th class="text-left px-4 py-2 text-gray-500">Kredit</th>
                <th class="text-right px-4 py-2 text-gray-500">Amount</th>
                <th class="text-left px-4 py-2 text-gray-500">Keterangan</th>
            </tr></thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($invoice->journalEntries as $je)
                <tr class="hover:bg-gray-50/60">
                    <td class="px-4 py-2 font-mono text-gray-600">{{ $je->reference }}</td>
                    <td class="px-4 py-2 text-blue-600 font-semibold">{{ $je->debit_account }}</td>
                    <td class="px-4 py-2 text-green-600 font-semibold">{{ $je->credit_account }}</td>
                    <td class="px-4 py-2 text-right font-mono">Rp{{ number_format($je->amount,0,',','.') }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $je->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Audit Log --}}
    @if($auditLogs->count() > 0)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/80">
            <h3 class="font-semibold text-gray-700 text-sm">Log Audit (F-21)</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($auditLogs->take(5) as $log)
            <div class="px-4 py-3 flex items-start gap-3">
                <span class="text-base">📝</span>
                <div class="flex-1 min-w-0">
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
