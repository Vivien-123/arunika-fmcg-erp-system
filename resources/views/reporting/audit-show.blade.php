@extends('layouts.app')
@section('title','Audit — '.$docId)
@php
    $currentApp  = 'Reporting';
    $sidebarMenu = [
        ['label' => 'Audit Log',   'url' => route('reporting.audit'),  'icon' => '📋'],
        ['label' => 'Dashboard',   'url' => route('home'),             'icon' => '📈'],
    ];

    $actionColors = [
        'created'               => 'bg-gray-100 text-gray-600',
        'login'                 => 'bg-blue-100 text-blue-700',
        'logout'                => 'bg-gray-100 text-gray-500',
        'status_changed'        => 'bg-yellow-100 text-yellow-700',
        'sign_off_authorized'   => 'bg-green-100 text-green-700',
        'sign_off_rejected'     => 'bg-red-100 text-red-700',
        'access_denied'         => 'bg-red-100 text-red-700',
        'private_access_denied' => 'bg-orange-100 text-orange-700',
        'confirm'               => 'bg-blue-100 text-blue-700',
        'validate'              => 'bg-green-100 text-green-700',
        'post'                  => 'bg-purple-100 text-purple-700',
        'payment'               => 'bg-emerald-100 text-emerald-700',
        'return'                => 'bg-rose-100 text-rose-700',
        'rejected_edit'         => 'bg-red-100 text-red-600',
        'double_post_blocked'   => 'bg-red-100 text-red-700',
    ];

    /* Type label mapping */
    $typeLabels = [
        'employee'       => '👤 Employee',
        'access_control' => '🔐 Access Control',
        'purchase_order' => '📦 Purchase Order',
        'sales_order'    => '🛒 Sales Order',
        'delivery'       => '🚚 Delivery',
        'receipt'        => '📥 Receipt',
        'customer_invoice' => '🧾 Customer Invoice',
        'vendor_bill'    => '📄 Vendor Bill',
        'payment'        => '💰 Payment',
        'credit_note'    => '↩️ Credit Note',
        'transfer'       => '🔄 Transfer',
    ];
@endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-400">›</span>
    <a href="{{ route('reporting.audit') }}" class="hover:text-[#714B67]">Audit Log</a>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700 font-medium font-mono">{{ $docId }}</span>
@endsection

@section('smart_buttons')
    {{-- Smart buttons — F-21: related document counts --}}
    <a href="{{ route('reporting.audit') }}?document_ref={{ urlencode($docId) }}"
       class="inline-flex flex-col items-center px-3 py-1.5 border rounded text-xs bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100 transition">
        <span class="text-base">📋</span>
        <span class="font-bold text-sm">{{ $logs->count() }}</span>
        <span>Log Entries</span>
    </a>

    @foreach($related as $relType => $relCount)
    <span class="inline-flex flex-col items-center px-3 py-1.5 border rounded text-xs
        {{ $relCount > 0 ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-gray-50 border-gray-200 text-gray-400' }}">
        <span class="text-base">{{ $relCount > 0 ? '🔗' : '○' }}</span>
        <span class="font-bold text-sm">{{ $relCount }}</span>
        <span class="capitalize">{{ str_replace('_', ' ', $relType) }}</span>
    </span>
    @endforeach
@endsection

@section('status_bar')
    @php
        $lastLog   = $logs->first();
        $firstLog  = $logs->last();
        $statusMap = [
            'created'  => 'Draft',
            'confirm'  => 'Confirmed',
            'validate' => 'Done',
            'post'     => 'Posted',
            'payment'  => 'Paid',
        ];
        $lastStatus = $lastLog ? ($statusMap[$lastLog->action] ?? ucfirst($lastLog->action)) : 'Unknown';
        $stages     = ['Draft', 'Confirmed', 'Done / Posted', 'Paid'];
    @endphp
    <x-status-bar :stages="$stages" :current="$lastStatus" />
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-4">

    {{-- ③ Fields — S-18: Document, Actor, Action, Timestamp --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-[#714B67] flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                📋
            </div>
            <div>
                <h2 class="font-bold text-gray-800 text-base font-mono">{{ $docId }}</h2>
                <p class="text-xs text-gray-500">
                    {{ $typeLabels[$docType] ?? ucfirst(str_replace('_', ' ', $docType)) }}
                    · {{ $logs->count() }} log entries
                </p>
            </div>
        </div>
        <div class="px-6 py-4 grid grid-cols-2 gap-4 text-sm">
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Document</label>
                <p class="font-mono font-semibold text-gray-800">{{ $docId }}</p>
            </div>
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Tipe Dokumen</label>
                <p class="text-gray-700">{{ $typeLabels[$docType] ?? ucfirst(str_replace('_', ' ', $docType)) }}</p>
            </div>
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Aksi Terakhir</label>
                @if($lastLog)
                @php $cls = $actionColors[$lastLog->action] ?? 'bg-gray-100 text-gray-600'; @endphp
                <span class="inline-block px-2 py-0.5 rounded text-xs font-mono font-semibold {{ $cls }}">
                    {{ $lastLog->action }}
                </span>
                @else
                <span class="text-gray-400 italic">—</span>
                @endif
            </div>
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Timestamp Terakhir</label>
                <p class="font-mono text-gray-700">
                    {{ $lastLog ? \Carbon\Carbon::parse($lastLog->acted_at)->format('d/m/Y H:i:s') : '—' }}
                </p>
            </div>
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Actor (User)</label>
                <p class="font-mono text-gray-700">{{ $lastLog?->actor_user ?? '—' }}</p>
            </div>
            <div>
                <label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Actor (Employee ID)</label>
                <p class="font-mono text-gray-700">{{ $lastLog?->actor_employee ?? '—' }}</p>
            </div>
        </div>
    </div>

    {{-- ④ Detail / KPI table — S-18: Reference, Action, Actor, Result --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-4 py-3 bg-gray-50 border-b flex items-center justify-between">
            <h3 class="text-xs font-semibold text-gray-600 uppercase tracking-wide">
                ④ Riwayat Aksi — {{ $docId }}
                <span class="font-normal text-gray-400 ml-1">(F-21 · SR-16 · SR-19)</span>
            </h3>
            <span class="text-xs text-gray-400">{{ $logs->count() }} entri</span>
        </div>
        <table class="w-full text-xs">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Timestamp</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Reference</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Aksi</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Actor</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Employee</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Hasil / Deskripsi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                @php $cls = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-600'; @endphp
                <tr class="border-b last:border-0 hover:bg-gray-50">
                    <td class="px-4 py-2 font-mono text-gray-500 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($log->acted_at)->format('d/m/Y H:i:s') }}
                    </td>
                    <td class="px-4 py-2 font-mono font-semibold text-[#714B67]">{{ $log->document_id }}</td>
                    <td class="px-4 py-2">
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-mono font-semibold {{ $cls }}">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="px-4 py-2 font-mono text-gray-700">{{ $log->actor_user }}</td>
                    <td class="px-4 py-2 font-mono text-gray-500">{{ $log->actor_employee ?? '—' }}</td>
                    <td class="px-4 py-2 text-gray-600 max-w-xs">{{ $log->description ?: '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-8 text-gray-400 italic">
                        Tidak ada log untuk dokumen ini.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Related Docs smart buttons section — S-18 ⑤ / F-21 --}}
    @if(count($related))
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
            ⑤ Related Documents — Smart Buttons (F-21 · T-35)
        </h4>
        <div class="flex flex-wrap gap-3">
            @foreach($related as $relType => $relCount)
            <div class="inline-flex flex-col items-center px-4 py-2 border rounded-lg text-xs
                {{ $relCount > 0 ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-gray-50 border-gray-200 text-gray-400' }}">
                <span class="font-bold text-lg">{{ $relCount }}</span>
                <span class="capitalize font-medium">{{ str_replace('_', ' ', $relType) }}</span>
                @if($relCount === 0)
                <span class="text-[10px] text-gray-400 mt-0.5">Belum dibuat</span>
                @else
                <span class="text-[10px] text-blue-500 mt-0.5">Lihat →</span>
                @endif
            </div>
            @endforeach
        </div>
        <p class="text-[10px] text-gray-400 mt-3 italic">
            T-35: PO00007 → Receipt = 0, Bill = 0 (seed baseline, AP Rp700.000 tetap).
            T-36: Re-validasi setelah Done → diblokir idempotent (SR-16).
        </p>
    </div>
    @endif

    {{-- Old/New values diff — useful for status_changed / rejected_edit --}}
    @php
        $logsWithDiff = $logs->filter(fn($l) => $l->old_values || $l->new_values);
    @endphp
    @if($logsWithDiff->isNotEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4">
        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">
            Perubahan Nilai (Before / After)
        </h4>
        <div class="space-y-2">
            @foreach($logsWithDiff as $log)
            <div class="border border-gray-100 rounded-lg px-3 py-2 text-xs">
                <div class="flex items-center gap-2 mb-1">
                    @php $cls = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-600'; @endphp
                    <span class="font-mono text-gray-400">
                        {{ \Carbon\Carbon::parse($log->acted_at)->format('d/m/Y H:i') }}
                    </span>
                    <span class="inline-block px-2 py-0.5 rounded text-[10px] font-mono {{ $cls }}">
                        {{ $log->action }}
                    </span>
                    <span class="text-gray-500">oleh <strong>{{ $log->actor_user }}</strong></span>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    @if($log->old_values)
                    <div class="bg-red-50 rounded px-2 py-1">
                        <p class="text-[10px] font-semibold text-red-600 uppercase mb-1">Before</p>
                        @foreach($log->old_values as $k => $v)
                        <p class="font-mono text-red-700">{{ $k }}: {{ $v }}</p>
                        @endforeach
                    </div>
                    @endif
                    @if($log->new_values)
                    <div class="bg-green-50 rounded px-2 py-1">
                        <p class="text-[10px] font-semibold text-green-600 uppercase mb-1">After</p>
                        @foreach($log->new_values as $k => $v)
                        <p class="font-mono text-green-700">{{ $k }}: {{ $v }}</p>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Back link --}}
    <div class="text-xs">
        <a href="{{ route('reporting.audit') }}" class="text-[#714B67] hover:underline">
            ← Kembali ke Audit Log
        </a>
    </div>
</div>
@endsection
