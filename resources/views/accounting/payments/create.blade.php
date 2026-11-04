@extends('layouts.app')
@section('title','Register Payment')
@php $currentApp = 'Accounting'; $sidebarMenu = [
    ['label'=>'Customer Invoices','url'=>route('accounting.invoices.index'),'icon'=>'🧾'],
    ['label'=>'Vendor Bills',     'url'=>route('accounting.bills.index'),   'icon'=>'📄'],
]; @endphp

@section('content')
<div class="max-w-lg mx-auto px-2">
    <div class="mb-5">
        <h1 class="text-xl font-bold text-gray-800">Register Payment</h1>
        <p class="text-xs text-gray-400 mt-0.5">S-12 · F-15 · SR-20: Pembayaran ≤ Outstanding</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-2.5 rounded-lg mb-4">❌ {{ $errors->first() }}</div>
    @endif

    {{-- Invoice summary --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
        <div class="flex justify-between items-center mb-3">
            <div>
                <p class="font-mono font-bold text-[#714B67]">{{ $invoice->document_number }}</p>
                <p class="text-xs text-gray-500">{{ $invoice->partner_name }}</p>
            </div>
            <div class="text-right">
                <p class="text-[10px] text-gray-400">Outstanding</p>
                <p class="text-lg font-bold text-red-600">Rp{{ number_format($invoice->outstandingAmount(),0,',','.') }}</p>
            </div>
        </div>
        <div class="bg-orange-50 border border-orange-200 rounded-lg px-3 py-2 text-[11px] text-orange-700">
            ⚠ SR-20: Maksimum pembayaran = Rp{{ number_format($invoice->outstandingAmount(),0,',','.') }}
        </div>
    </div>

    <form method="POST" action="{{ route('accounting.payments.store', $invoice->id) }}">
        @csrf
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
            <div class="mb-3">
                <label class="text-xs text-gray-500 font-medium">Jumlah Pembayaran (Rp) *</label>
                <input name="amount" type="number" min="1" max="{{ $invoice->outstandingAmount() }}"
                       value="{{ old('amount', $invoice->outstandingAmount()) }}" required
                       class="w-full mt-1 text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#714B67] font-mono">
            </div>
            <div>
                <label class="text-xs text-gray-500 font-medium">Referensi Pembayaran *</label>
                <input name="payment_ref" value="{{ old('payment_ref','PAY-'.str_pad(rand(1,99999),5,'0',STR_PAD_LEFT)) }}" required
                       class="w-full mt-1 text-xs border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#714B67] font-mono">
            </div>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-5 py-2.5 rounded-lg shadow transition">
                Catat Pembayaran
            </button>
            <a href="javascript:history.back()" class="text-xs text-gray-500 hover:text-gray-700 px-4 py-2.5">Batal</a>
        </div>
    </form>
</div>
@endsection
