@extends('layouts.app')
@section('title','Chart of Accounts')
@php $currentApp = 'Accounting'; $sidebarMenu = [
    ['label'=>'Customer Invoices','url'=>route('accounting.invoices.index'),'icon'=>'🧾'],
    ['label'=>'Vendor Bills',     'url'=>route('accounting.bills.index'),   'icon'=>'📄'],
    ['label'=>'General Ledger',   'url'=>route('accounting.ledger.index'),  'icon'=>'📊'],
    ['label'=>'Chart of Accounts','url'=>route('accounting.coa.index'),     'icon'=>'📋'],
]; @endphp

@section('content')
<div class="max-w-3xl mx-auto px-2">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-800">Chart of Accounts</h1>
        <p class="text-xs text-gray-400 mt-0.5">Handout §4 · 15 akun COA · Rentang 1000–6100</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-xs">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-semibold">Kode</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-semibold">Nama Akun</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-semibold">Tipe</th>
                    <th class="text-center px-4 py-3 text-gray-500 font-semibold">Normal Balance</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-semibold">Saldo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($accounts->groupBy('type') as $type => $accts)
                <tr class="bg-gray-50/80">
                    <td colspan="5" class="px-4 py-2 text-[10px] font-bold text-gray-500 uppercase tracking-widest">{{ $type }}</td>
                </tr>
                @foreach($accts as $acc)
                <tr class="hover:bg-gray-50/60 transition">
                    <td class="px-4 py-2.5 font-mono font-bold text-[#714B67]">{{ $acc->code }}</td>
                    <td class="px-4 py-2.5 text-gray-700">{{ $acc->name }}</td>
                    <td class="px-4 py-2.5 text-gray-500">{{ $acc->type }}</td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold {{ $acc->normal_balance=='Debit'?'bg-blue-100 text-blue-700':'bg-green-100 text-green-700' }}">
                            {{ $acc->normal_balance }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-right font-mono text-gray-700">
                        Rp{{ number_format($acc->computeBalance(),0,',','.') }}
                    </td>
                </tr>
                @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
