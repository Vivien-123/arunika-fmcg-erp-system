@extends('layouts.app')
@section('title','Buat Credit Note')
@php $currentApp = 'Accounting'; $sidebarMenu = [
    ['label'=>'Customer Invoices','url'=>route('accounting.invoices.index'),'icon'=>'🧾'],
]; @endphp

@section('content')
<div class="max-w-3xl mx-auto px-2">
    <div class="mb-5">
        <p class="text-xs text-gray-400 mb-1"><a href="{{ route('accounting.invoices.show', $invoice->id) }}" class="hover:underline">{{ $invoice->document_number }}</a> › Credit Note</p>
        <h1 class="text-xl font-bold text-gray-800">Buat Credit Note</h1>
        <p class="text-xs text-gray-400 mt-0.5">S-16 · F-16 · SR-13: Max qty = invoiced − already credited</p>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 text-xs px-4 py-2.5 rounded-lg mb-4">❌ {{ $errors->first() }}</div>
    @endif

    <div class="bg-orange-50 border border-orange-200 rounded-xl px-4 py-3 mb-4 text-xs text-orange-700">
        ⚠ SR-13: Credit Note maksimal qty yang sudah ditagih dan belum dikreditkan.
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
        <h3 class="font-semibold text-gray-700 text-sm mb-3">Batas Qty yang Dapat Dikreditkan</h3>
        <table class="w-full text-xs">
            <thead class="bg-gray-50"><tr>
                <th class="text-left px-3 py-2 text-gray-500">SKU</th>
                <th class="text-right px-3 py-2 text-gray-500">Max Creditable Qty</th>
            </tr></thead>
            <tbody>
                @foreach($maxQty as $sku => $qty)
                <tr class="border-t border-gray-50">
                    <td class="px-3 py-2 font-mono text-gray-600">{{ $sku }}</td>
                    <td class="px-3 py-2 text-right font-bold {{ $qty > 0 ? 'text-green-600' : 'text-red-400' }}">{{ $qty }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <form method="POST" action="{{ route('accounting.credit-notes.store', $invoice->id) }}">
        @csrf
        @foreach($invoice->items as $i => $item)
        @if(($maxQty[$item->variant_sku] ?? 0) > 0)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-3">
            <p class="font-mono font-semibold text-[#714B67] text-xs mb-3">{{ $item->variant_sku }} — {{ $item->product_name }}</p>
            <input type="hidden" name="return_lines[{{ $i }}][variant_sku]" value="{{ $item->variant_sku }}">
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="text-[10px] text-gray-400">Return Qty (max {{ $maxQty[$item->variant_sku] }})</label>
                    <input name="return_lines[{{ $i }}][qty]" type="number" min="0" max="{{ $maxQty[$item->variant_sku] }}" value="0"
                           class="w-full mt-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5">
                </div>
                <div>
                    <label class="text-[10px] text-gray-400">Layak Jual?</label>
                    <select name="return_lines[{{ $i }}][is_saleable]" class="w-full mt-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5">
                        <option value="1">Ya (reversal COGS)</option>
                        <option value="0">Tidak</option>
                    </select>
                </div>
                <div>
                    <label class="text-[10px] text-gray-400">Unit Cost (Rp)</label>
                    <input name="return_lines[{{ $i }}][unit_cost]" type="number" value="0"
                           class="w-full mt-1 text-xs border border-gray-200 rounded-lg px-2 py-1.5">
                </div>
            </div>
        </div>
        @endif
        @endforeach

        <div class="flex gap-3">
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold px-5 py-2.5 rounded-lg shadow transition">
                Buat & Post Credit Note
            </button>
            <a href="{{ route('accounting.invoices.show', $invoice->id) }}" class="text-xs text-gray-500 hover:text-gray-700 px-4 py-2.5">Batal</a>
        </div>
    </form>
</div>
@endsection
