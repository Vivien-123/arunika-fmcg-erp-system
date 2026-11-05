@extends('layouts.app')
@section('title', 'Master Admin — Business Rules')
@php $currentApp = 'Master Admin'; @endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-300">/</span><span class="text-gray-400">Master Admin</span>
    <span class="mx-1 text-gray-300">/</span><span class="text-gray-700 font-medium">Business Rules (SR-01..SR-22)</span>
@endsection

@section('control_panel_extra')
    <button class="text-xs text-gray-600 border border-gray-300 rounded px-3 py-1 hover:bg-gray-50 transition">Export CSV</button>
@endsection

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-gray-800">Master Admin &amp; Business Rule Configurator</h1>
        <p class="text-sm text-gray-500 mt-1">SR-01 s.d. SR-22 · Permission Matrix · User-Employee Mapping (SR-18)</p>
    </div>

    {{-- Role Access Matrix --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">Role Access Matrix</h2>
            <p class="text-xs text-gray-400 mt-0.5">Hak akses CRUD + Approve + Export per Role (CheckRole middleware)</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-[10px] uppercase font-bold text-gray-500 tracking-wide border-b">
                        <th class="text-left px-4 py-2">Role</th>
                        <th class="text-center px-4 py-2">Sales/CRM</th>
                        <th class="text-center px-4 py-2">Purchase</th>
                        <th class="text-center px-4 py-2">Inventory</th>
                        <th class="text-center px-4 py-2">Accounting</th>
                        <th class="text-center px-4 py-2">Employees</th>
                        <th class="text-center px-4 py-2">Audit Log</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($roleMatrix as $row)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2.5 font-semibold text-gray-800">{{ $row['role'] }}</td>
                        <td class="px-4 py-2.5 text-center text-xs">{{ $row['sales'] }}</td>
                        <td class="px-4 py-2.5 text-center text-xs">{{ $row['purchase'] }}</td>
                        <td class="px-4 py-2.5 text-center text-xs">{{ $row['inventory'] }}</td>
                        <td class="px-4 py-2.5 text-center text-xs">{{ $row['accounting'] }}</td>
                        <td class="px-4 py-2.5 text-center text-xs">{{ $row['employees'] }}</td>
                        <td class="px-4 py-2.5 text-center text-xs">{{ $row['audit'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- User Accounts & Employee Mapping (SR-18) --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden mb-6">
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700">User Accounts &amp; Employee Mapping (SR-18)</h2>
            <p class="text-xs text-gray-400 mt-0.5">Pemetaan User Account ke Employee Aktif · Hanya Employee Aktif yang boleh sign-off</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-[10px] uppercase font-bold text-gray-500 tracking-wide border-b">
                        <th class="text-left px-4 py-2">Username</th>
                        <th class="text-left px-4 py-2">Name</th>
                        <th class="text-left px-4 py-2">Role</th>
                        <th class="text-left px-4 py-2">Mapped Employee</th>
                        <th class="text-center px-4 py-2">Employee Status</th>
                        <th class="text-center px-4 py-2">Can Sign-off (SR-17)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($userAccounts as $ua)
                    @php $emp = $ua->employee; @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-2.5 text-xs mono text-[#714B67] font-semibold">{{ $ua->username }}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-700">{{ $ua->name }}</td>
                        <td class="px-4 py-2.5"><span class="text-[10px] font-semibold px-2 py-0.5 rounded {{ $ua->role==='Owner'?'bg-purple-100 text-purple-700':'bg-gray-100 text-gray-600' }}">{{ $ua->role }}</span></td>
                        <td class="px-4 py-2.5 text-xs text-gray-600">{{ $emp ? $emp->id.' — '.$emp->name : '—' }}</td>
                        <td class="px-4 py-2.5 text-center">
                            @if($emp)
                                @if($emp->status === 'Aktif')
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-green-100 text-green-700">Aktif</span>
                                @else
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-red-100 text-red-700">Nonaktif</span>
                                @endif
                            @else
                            <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2.5 text-center">
                            @if($emp && $emp->status === 'Aktif' && $ua->canSignOffWarehouse())
                            <span class="text-green-600 text-sm">✓</span>
                            @else
                            <span class="text-gray-300 text-sm">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 22 Business Rules Table --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-700">22 Business Rules (SR-01 s.d. SR-22)</h2>
                <p class="text-xs text-gray-400 mt-0.5">Klik baris untuk lihat detail · Status: Active / Rejected / Modified</p>
            </div>
            <span class="text-xs bg-gray-100 text-gray-600 font-semibold px-2.5 py-1 rounded-full">{{ count($businessRules) }} Rules</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-[10px] uppercase font-bold text-gray-500 tracking-wide border-b sticky-th">
                        <th class="text-left px-4 py-2 w-16">ID</th>
                        <th class="text-left px-4 py-2">Rule Name</th>
                        <th class="text-left px-4 py-2">Stakeholder</th>
                        <th class="text-left px-4 py-2">Module</th>
                        <th class="text-center px-4 py-2 w-24">Status</th>
                        <th class="text-left px-4 py-2">Action / Implementation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($businessRules as $rule)
                    @php
                        $statusColors = [
                            'Active' => 'bg-green-100 text-green-700',
                            'Rejected' => 'bg-red-100 text-red-700',
                            'Modified' => 'bg-yellow-100 text-yellow-700',
                        ];
                        $sc = $statusColors[$rule['status']] ?? 'bg-gray-100 text-gray-600';
                    @endphp
                    <tr class="hover:bg-gray-50 transition cursor-pointer" onclick="openRuleModal('{{ $rule['id'] }}')">
                        <td class="px-4 py-2.5 text-xs mono font-bold text-[#714B67]">{{ $rule['id'] }}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-700">{{ $rule['name'] }}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-500">{{ $rule['stakeholder'] }}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-500">{{ $rule['module'] }}</td>
                        <td class="px-4 py-2.5 text-center"><span class="text-[10px] font-semibold px-2 py-0.5 rounded {{ $sc }}">{{ $rule['status'] }}</span></td>
                        <td class="px-4 py-2.5 text-xs text-gray-500">{{ $rule['action'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Edit Rule Confirmation Modal --}}
<div id="ruleModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeRuleModal()"></div>
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[520px] max-w-[90vw] bg-white rounded-lg shadow-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-red-50">
            <h3 class="text-base font-semibold text-red-700">Edit Business Rule — <span id="ruleIdLabel" class="mono">SR-XX</span></h3>
            <button onclick="closeRuleModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div>
                <label class="text-xs text-gray-500 font-semibold">Alasan Perubahan (Change Reason)</label>
                <p class="text-[10px] text-gray-400 mb-2">Minimum 10 karakter · Log perubahan akan dicatat ke Audit Trail</p>
                <textarea id="changeReason" rows="3" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:border-[#714B67] focus:ring-1 focus:ring-[#714B67]" placeholder="Jelaskan alasan perubahan rule ini..." oninput="checkReasonLength()"></textarea>
                <p id="reasonCounter" class="text-[10px] text-gray-400 mt-1">0 / 10 karakter minimum</p>
            </div>
            <div class="flex items-center gap-2 pt-2">
                <button id="confirmBtn" disabled class="bg-[#714B67] text-white text-sm font-medium px-4 py-2 rounded opacity-50 cursor-not-allowed transition">Confirm</button>
                <button onclick="closeRuleModal()" class="border border-gray-300 text-gray-600 text-sm px-4 py-2 rounded hover:bg-gray-50 transition">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
function openRuleModal(id) {
    document.getElementById('ruleIdLabel').textContent = id;
    document.getElementById('ruleModal').classList.remove('hidden');
    document.getElementById('changeReason').value = '';
    checkReasonLength();
}
function closeRuleModal() { document.getElementById('ruleModal').classList.add('hidden'); }
function checkReasonLength() {
    const len = document.getElementById('changeReason').value.length;
    const btn = document.getElementById('confirmBtn');
    const counter = document.getElementById('reasonCounter');
    counter.textContent = len + ' / 10 karakter minimum';
    if (len >= 10) { btn.disabled = false; btn.classList.remove('opacity-50','cursor-not-allowed'); }
    else { btn.disabled = true; btn.classList.add('opacity-50','cursor-not-allowed'); }
}
</script>
@endsection
