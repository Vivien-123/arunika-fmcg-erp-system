@extends('layouts.app')
@section('title','Vendor Bills')
@php $currentApp = 'Accounting'; $sidebarMenu = [
    ['label'=>'Customer Invoices','url'=>route('accounting.invoices.index'),'icon'=>'🧾'],
    ['label'=>'Vendor Bills',     'url'=>route('accounting.bills.index'),   'icon'=>'📄'],
    ['label'=>'General Ledger',   'url'=>route('accounting.ledger.index'),  'icon'=>'📊'],
    ['label'=>'Chart of Accounts','url'=>route('accounting.coa.index'),     'icon'=>'📋'],
]; @endphp

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h1 class="page-title">Vendor Bills</h1>
            <p class="page-sub">S-11 · F-13 · SR-06: 3-Way Match PO + Receipt + Bill</p>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-xs px-4 py-2.5 rounded-lg mb-4">✅ {{ session('success') }}</div>
    @endif

    <div class="panel overflow-hidden">
        <table class="w-full text-xs">
            <thead class="border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wide">No. Dokumen</th>
                    <th class="text-left px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Vendor</th>
                    <th class="text-left px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Source PO</th>
                    <th class="text-right px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Net</th>
                    <th class="text-right px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Gross</th>
                    <th class="text-right px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Outstanding</th>
                    <th class="text-center px-4 py-3 text-[10px] font-bold text-gray-400 uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($invoices as $inv)
                <tr class="tr-hover">
                    <td class="px-4 py-3 font-mono font-semibold text-[#714B67]">{{ $inv->document_number }}</td>
                    <td class="px-4 py-3 text-gray-700">{{ $inv->partner_name }}</td>
                    <td class="px-4 py-3 font-mono text-gray-500">{{ $inv->source_document_id ?? '-' }}</td>
                    <td class="px-4 py-3 text-right">Rp{{ number_format($inv->untaxed_amount,0,',','.') }}</td>
                    <td class="px-4 py-3 text-right font-semibold">Rp{{ number_format($inv->gross_amount,0,',','.') }}</td>
                    <td class="px-4 py-3 text-right {{ $inv->amount_outstanding > 0 ? 'text-red-600 font-semibold' : 'text-green-600' }}">
                        Rp{{ number_format($inv->amount_outstanding,0,',','.') }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php $colors = ['draft'=>'bg-yellow-100 text-yellow-700','posted'=>'bg-blue-100 text-blue-700','paid'=>'bg-green-100 text-green-700']; @endphp
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $colors[$inv->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ strtoupper($inv->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('accounting.bills.show', $inv->id) }}" class="text-[#714B67] hover:underline font-medium">Lihat →</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Belum ada vendor bill.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $invoices->links() }}</div>
</div>
@endsection
