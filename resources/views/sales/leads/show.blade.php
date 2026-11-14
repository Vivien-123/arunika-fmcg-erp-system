@extends('layouts.app')
@section('title', 'Lead '.$lead->id)
@php
    $currentApp  = 'CRM';
    $sidebarMenu = [
        ['label'=>'Pipeline', 'url'=>route('crm.leads.index'), 'icon'=>'📋'],
    ];
    $stageFlow = ['new'=>0,'qualified'=>1,'won'=>2];
    $currentStageIdx = $stageFlow[$lead->stage] ?? 0;
@endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-400">›</span>
    <a href="{{ route('crm.leads.index') }}" class="text-[#714B67] hover:underline">CRM</a>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700 font-medium">{{ $lead->id }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto px-2">

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded px-4 py-2 mb-3 text-sm">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded px-4 py-2 mb-3 text-sm">⛔ {{ session('error') }}</div>
    @endif

    <div class="bg-white border rounded-xl shadow-sm overflow-hidden">

        {{-- ① Title bar --}}
        <div class="flex items-center justify-between px-5 py-4 border-b bg-gray-50">
            <div>
                <span class="text-xs text-gray-400 font-mono">F-04 · S-04 · SR-19</span>
                <h1 class="text-lg font-bold text-gray-800 mt-0.5">{{ $lead->id }} — {{ $lead->partner_name ?? 'Tanpa Pelanggan' }}</h1>
            </div>
            {{-- ② Status bar --}}
            <div class="flex items-center gap-1 text-xs font-semibold">
                @foreach(['new'=>'New','qualified'=>'Qualified','won'=>'Won'] as $s => $label)
                <span class="px-3 py-1.5 rounded-full {{ $lead->stage === $s ? 'bg-[#714B67] text-white' : 'bg-gray-100 text-gray-500' }}">
                    {{ $label }}
                </span>
                @if(!($s === 'won')) <span class="text-gray-300">›</span> @endif
                @endforeach
            </div>
        </div>

        {{-- ③ Fields --}}
        <div class="grid grid-cols-2 gap-4 px-5 py-4">
            <div>
                <p class="text-xs text-gray-400">Lead</p>
                <p class="font-mono font-semibold text-gray-700">{{ $lead->id }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Customer</p>
                <p class="font-semibold text-gray-700">
                    {{ $lead->partner_id ? $lead->partner_id.' — '.$lead->partner_name : '— Belum ditetapkan —' }}
                </p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Expected Revenue</p>
                <p class="font-semibold text-gray-700">Rp{{ number_format($lead->expected_revenue) }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Salesperson</p>
                <p class="font-semibold text-gray-700">{{ $lead->salesperson_name ?? '—' }}</p>
            </div>
            @if($lead->notes)
            <div class="col-span-2">
                <p class="text-xs text-gray-400">Notes</p>
                <p class="text-gray-700 text-sm">{{ $lead->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Action buttons --}}
        <div class="px-5 py-3 border-t bg-gray-50 flex flex-wrap gap-2">
            @if(auth()->user()->canEdit())
            @if($lead->stage === 'new')
            <form method="POST" action="{{ route('crm.leads.convert', $lead->id) }}">
                @csrf
                <button type="submit"
                        class="bg-[#714B67] text-white px-4 py-1.5 rounded text-sm hover:bg-[#5c3d56] transition font-medium">
                    ▶ Konversi ke Opportunity
                </button>
            </form>
            @endif

            {{-- SR-19 demo: test remove customer --}}
            @if($lead->partner_id)
            <form method="POST" action="{{ route('crm.leads.update-customer', $lead->id) }}"
                  onsubmit="return confirm('Hapus relasi pelanggan? (Akan ditolak jika status Qualified - SR-19)')">
                @csrf
                <input type="hidden" name="partner_id" value="">
                <button type="submit"
                        class="border border-red-300 text-red-600 px-3 py-1.5 rounded text-sm hover:bg-red-50 transition">
                    🗑 Hapus Pelanggan (T-08 test)
                </button>
            </form>
            @endif
            @endif

            <a href="{{ route('crm.leads.index') }}" class="text-gray-500 text-sm px-3 py-1.5 hover:underline">
                ← Kembali ke Pipeline
            </a>
        </div>

        {{-- ⑤ Related Docs & Audit Log --}}
        <div class="px-5 py-4 border-t">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">⑤ Audit / History</h3>
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
                            <td class="py-1 text-gray-500">{{ Str::limit($log->description, 80) }}</td>
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
