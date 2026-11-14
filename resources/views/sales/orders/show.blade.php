@extends('layouts.app')
@section('title', 'SO '.$so->id)
@php
    $currentApp  = 'Sales';
    $sidebarMenu = [
        ['label'=>'Semua SO', 'url'=>route('sales.orders.index'), 'icon'=>'📋'],
        ['label'=>'Buat Quotation', 'url'=>route('sales.orders.create'), 'icon'=>'➕'],
    ];
    $statusBar   = ['draft'=>0,'to_approve'=>1,'confirmed'=>2,'done'=>3];
    $statusColors = [
        'draft'      => 'bg-gray-100 text-gray-600',
        'to_approve' => 'bg-yellow-100 text-yellow-800',
        'confirmed'  => 'bg-green-100 text-green-700',
        'done'       => 'bg-blue-100 text-blue-700',
        'cancelled'  => 'bg-red-100 text-red-600',
    ];
    $user = auth()->user();
    $isLocked = $so->isLocked();
@endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-400">›</span>
    <a href="{{ route('sales.orders.index') }}" class="text-[#714B67] hover:underline">Sales</a>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700 font-medium">{{ $so->id }}</span>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-2">

    @foreach(['success','warning','error'] as $type)
    @if(session($type))
    <div class="rounded px-4 py-2 mb-3 text-sm border
        {{ $type==='success'?'bg-green-50 border-green-200 text-green-800':($type==='warning'?'bg-yellow-50 border-yellow-200 text-yellow-800':'bg-red-50 border-red-200 text-red-800') }}">
        {{ $type==='success'?'✅':($type==='warning'?'⚠️':'⛔') }} {{ session($type) }}
    </div>
    @endif
    @endforeach

    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">

        {{-- ① Title + Status bar --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 px-5 py-4 border-b bg-gray-50">
            <div>
                <span class="text-xs text-gray-400">F-05, F-06, F-10, F-22 · S-05</span>
                <h1 class="text-lg font-bold text-gray-800 mt-0.5">{{ $so->id }}</h1>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $statusColors[$so->status]??'bg-gray-100 text-gray-500' }}">
                    {{ ucfirst(str_replace('_',' ',$so->status)) }}
                </span>
                @if($so->isLocked())
                    <span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full ml-1">🔒 Harga Terkunci (SR-12)</span>
                @endif
            </div>
            {{-- ② Status breadcrumb --}}
            <div class="flex items-center gap-1 text-xs font-semibold">
                @foreach(['draft'=>'Draft','to_approve'=>'To Approve','confirmed'=>'Confirmed','done'=>'Done'] as $s => $l)
                <span class="px-2 py-1 rounded {{ $so->status===$s?'bg-[#714B67] text-white':'bg-gray-100 text-gray-500' }}">{{ $l }}</span>
                @if($s!=='done')<span class="text-gray-300">›</span>@endif
                @endforeach
            </div>
        </div>

        {{-- Smart Buttons --}}
        <div class="flex flex-wrap gap-3 px-5 py-3 border-b bg-gray-50">
            <div class="flex flex-col items-center bg-white border rounded px-3 py-2 text-xs text-gray-600 min-w-[70px]">
                <span class="text-lg font-bold text-[#714B67]">{{ $relatedDocs['delivery']??0 }}</span>
                <span>Delivery</span>
            </div>
            <div class="flex flex-col items-center bg-white border rounded px-3 py-2 text-xs text-gray-600 min-w-[70px]">
                <span class="text-lg font-bold text-[#714B67]">{{ $relatedDocs['customer_invoice']??0 }}</span>
                <span>Invoice</span>
            </div>
            <div class="flex flex-col items-center bg-white border rounded px-3 py-2 text-xs text-gray-600 min-w-[100px]">
                <span class="text-sm font-bold {{ $exposure > $creditLimit ? 'text-red-600' : 'text-green-600' }}">
                    Rp{{ number_format($exposure) }}
                </span>
                <span>Exposure / Rp{{ number_format($creditLimit) }}</span>
            </div>
        </div>

        {{-- ③ Header fields --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 px-5 py-4 border-b text-sm">
            <div>
                <p class="text-xs text-gray-400">Customer</p>
                <p class="font-semibold text-gray-700">{{ $so->customer_id }} — {{ $so->customer_name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Document</p>
                <p class="font-mono font-bold text-[#714B67]">{{ $so->id }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Tgl Order</p>
                <p class="text-gray-700">{{ $so->order_date }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Janji Kirim (SR-03)</p>
                <p class="text-gray-700">{{ $so->delivery_promise_date ?? '—' }}</p>
            </div>
        </div>

        {{-- ④ Line items --}}
        <div class="px-5 py-4 border-b">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">④ Baris Produk</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-400 border-b uppercase tracking-wider">
                        <tr>
                            <th class="text-left py-2 pr-2">SKU</th>
                            <th class="text-right py-2 pr-2">Qty</th>
                            <th class="text-right py-2 pr-2">Harga</th>
                            <th class="text-right py-2 pr-2">Tax</th>
                            <th class="text-right py-2">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($so->lines as $line)
                        <tr class="hover:bg-gray-50">
                            <td class="py-2 pr-2">
                                <span class="font-mono text-xs text-[#714B67]">{{ $line->variant_sku }}</span>
                                <span class="text-gray-600 ml-1">{{ $line->product_name }}</span>
                                @if($line->discount_percent > 0)
                                <span class="ml-1 text-xs {{ $line->discount_percent > 10 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }} px-1.5 py-0.5 rounded-full">
                                    -{{ $line->discount_percent }}%
                                </span>
                                @endif
                                @if($isLocked)
                                <span class="ml-1 text-xs text-orange-400">🔒</span>
                                @endif
                            </td>
                            <td class="py-2 pr-2 text-right">{{ $line->quantity }} {{ $line->unit }}</td>
                            <td class="py-2 pr-2 text-right">Rp{{ number_format($line->unit_price) }}</td>
                            <td class="py-2 pr-2 text-right text-gray-400 text-xs">11%</td>
                            <td class="py-2 text-right font-semibold">Rp{{ number_format($line->gross_amount) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t bg-gray-50 text-sm">
                        <tr>
                            <td colspan="3" class="py-2 px-2 text-right text-gray-500">Subtotal (net):</td>
                            <td colspan="2" class="py-2 text-right font-semibold">Rp{{ number_format($so->untaxed_amount) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="py-1 px-2 text-right text-gray-400 text-xs">PPN 11%:</td>
                            <td colspan="2" class="py-1 text-right text-gray-500">Rp{{ number_format($so->tax_amount) }}</td>
                        </tr>
                        <tr class="border-t">
                            <td colspan="3" class="py-2 px-2 text-right font-bold text-gray-700">Total Gross:</td>
                            <td colspan="2" class="py-2 text-right font-bold text-[#714B67] text-base">Rp{{ number_format($so->gross_amount) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Discount approval notice --}}
        @if($so->isToApprove())
        <div class="mx-5 my-3 bg-yellow-50 border border-yellow-300 rounded-lg px-4 py-3">
            <p class="text-sm font-semibold text-yellow-800">⚠️ Menunggu Persetujuan Diskon (SR-07/08)</p>
            <p class="text-xs text-yellow-700 mt-0.5">Diskon &gt;10% ditemukan pada baris ini. Finance approval diperlukan sebelum SO dapat dikonfirmasi.</p>
            @if(in_array($user->role, ['Finance','MasterAdmin']))
            <div class="flex gap-2 mt-3">
                <form method="POST" action="{{ route('sales.orders.approve-discount', $so->id) }}">
                    @csrf
                    <button type="submit" class="bg-green-600 text-white px-4 py-1.5 rounded text-sm hover:bg-green-700">✅ Setujui Diskon</button>
                </form>
                <form method="POST" action="{{ route('sales.orders.reject-discount', $so->id) }}">
                    @csrf
                    <button type="submit" class="border border-red-300 text-red-600 px-4 py-1.5 rounded text-sm hover:bg-red-50">✕ Tolak</button>
                </form>
            </div>
            @else
            <p class="text-xs text-yellow-600 mt-2 italic">Hanya Finance yang dapat menyetujui.</p>
            @endif
        </div>
        @endif

        {{-- Action buttons --}}
        <div class="px-5 py-4 border-t bg-gray-50 flex flex-wrap gap-2">
            @if($so->isDraft() && in_array($user->role, ['Sales','MasterAdmin']))
            <form method="POST" action="{{ route('sales.orders.confirm', $so->id) }}">
                @csrf
                <button type="submit" class="bg-[#714B67] text-white px-4 py-2 rounded text-sm hover:bg-[#5c3d56] transition font-medium">
                    ✔ Konfirmasi SO
                </button>
            </form>
            @endif

            @if(!$so->isCancelled() && !$so->isDone() && in_array($user->role, ['Sales','MasterAdmin']))
            <form method="POST" action="{{ route('sales.orders.cancel', $so->id) }}"
                  onsubmit="return confirm('Batalkan SO {{ $so->id }}? Reservasi stok akan dilepaskan.')">
                @csrf
                <input type="hidden" name="reason" value="Dibatalkan oleh {{ $user->name }}">
                <button type="submit" class="border border-red-300 text-red-600 px-4 py-2 rounded text-sm hover:bg-red-50 transition">
                    ✕ Batalkan
                </button>
            </form>
            @endif

            <a href="{{ route('sales.orders.index') }}" class="text-gray-500 text-sm px-4 py-2 hover:underline">← Kembali</a>
        </div>

        {{-- ⑤ Related Docs / Log Notes / Audit --}}
        <div class="px-5 py-4 border-t">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">⑤ Related Docs | Log Notes | Audit / History</h3>
            @if($auditTrail->isEmpty())
                <p class="text-xs text-gray-400 italic">Belum ada aktivitas.</p>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-gray-600">
                    <thead>
                        <tr class="text-left text-gray-400 border-b">
                            <th class="py-1 pr-3">Waktu</th>
                            <th class="py-1 pr-3">Actor</th>
                            <th class="py-1 pr-3">Aksi</th>
                            <th class="py-1">Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($auditTrail as $log)
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="py-1 pr-3 whitespace-nowrap text-gray-400">{{ $log->acted_at->format('d/m/y H:i') }}</td>
                            <td class="py-1 pr-3 font-mono">{{ $log->actor_user }}</td>
                            <td class="py-1 pr-3">
                                <span class="bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded font-medium">{{ $log->action }}</span>
                            </td>
                            <td class="py-1 text-gray-500">{{ Str::limit($log->description, 90) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
