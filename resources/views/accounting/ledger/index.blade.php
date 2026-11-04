@extends('layouts.app')
@section('title','General Ledger')
@php $currentApp = 'Accounting'; $sidebarMenu = [
    ['label'=>'Customer Invoices','url'=>route('accounting.invoices.index'),'icon'=>'🧾'],
    ['label'=>'Vendor Bills',     'url'=>route('accounting.bills.index'),   'icon'=>'📄'],
    ['label'=>'General Ledger',   'url'=>route('accounting.ledger.index'),  'icon'=>'📊'],
    ['label'=>'Chart of Accounts','url'=>route('accounting.coa.index'),     'icon'=>'📋'],
]; @endphp

@section('content')
<div class="max-w-6xl mx-auto px-2">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-800">General Ledger / Journal Entries</h1>
        <p class="text-xs text-gray-400 mt-0.5">S-13 · F-14 · SR-16: Setiap entry idempotent</p>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex gap-2 mb-4 flex-wrap">
        <input name="reference" value="{{ request('reference') }}" placeholder="Referensi..." class="text-xs border border-gray-200 rounded-lg px-3 py-2">
        <select name="journal_type" class="text-xs border border-gray-200 rounded-lg px-3 py-2">
            <option value="">Semua Tipe</option>
            @foreach($types as $t)
            <option value="{{ $t }}" {{ request('journal_type')==$t?'selected':'' }}>{{ strtoupper($t) }}</option>
            @endforeach
        </select>
        <input name="date_from" type="date" value="{{ request('date_from') }}" class="text-xs border border-gray-200 rounded-lg px-3 py-2">
        <input name="date_to"   type="date" value="{{ request('date_to') }}"   class="text-xs border border-gray-200 rounded-lg px-3 py-2">
        <button class="bg-gray-100 hover:bg-gray-200 text-xs px-3 py-2 rounded-lg transition">Filter</button>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-xs">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left px-4 py-3 text-gray-500 font-semibold">Referensi</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-semibold">Tipe</th>
                    <th class="text-center px-4 py-3 text-gray-500 font-semibold">Debit</th>
                    <th class="text-center px-4 py-3 text-gray-500 font-semibold">Kredit</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-semibold">Amount</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-semibold">Keterangan</th>
                    <th class="text-left px-4 py-3 text-gray-500 font-semibold">Posted</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($entries as $e)
                <tr class="hover:bg-gray-50/60 transition {{ $e->is_reversed ? 'opacity-50 line-through' : '' }}">
                    <td class="px-4 py-2.5 font-mono text-[#714B67] font-semibold">{{ $e->reference }}</td>
                    <td class="px-4 py-2.5">
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold
                            {{ $e->journal_type=='sales'   ? 'bg-blue-100 text-blue-700'   :
                              ($e->journal_type=='purchase'? 'bg-orange-100 text-orange-700':
                              ($e->journal_type=='payment' ? 'bg-green-100 text-green-700' :
                              ($e->journal_type=='stock'   ? 'bg-purple-100 text-purple-700':
                                                             'bg-gray-100 text-gray-600'))) }}">
                            {{ strtoupper($e->journal_type) }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-center font-mono text-blue-700 font-semibold">{{ $e->debit_account }}</td>
                    <td class="px-4 py-2.5 text-center font-mono text-green-700 font-semibold">{{ $e->credit_account }}</td>
                    <td class="px-4 py-2.5 text-right font-mono font-semibold">Rp{{ number_format($e->amount,0,',','.') }}</td>
                    <td class="px-4 py-2.5 text-gray-500 max-w-xs truncate">{{ $e->description }}</td>
                    <td class="px-4 py-2.5 text-gray-400 whitespace-nowrap">{{ $e->posted_at?->format('d/m H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Belum ada journal entries.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $entries->links() }}</div>
</div>
@endsection
