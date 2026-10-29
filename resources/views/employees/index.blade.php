@extends('layouts.app')
@section('title','Employees')
@php
    $currentApp  = 'Employees';
    $sidebarMenu = [
        ['label'=>'Semua Pegawai','url'=>route('employees.index'),'icon'=>'👥'],
        ['label'=>'Departemen',   'url'=>route('employees.index').'?view=dept','icon'=>'🏢'],
    ];
    $hasFilter = request()->hasAny(['dept','status','search']);
@endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-400">›</span>
    <a href="{{ route('employees.index') }}" class="hover:text-[#714B67]">Employees</a>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700">Direktori Pegawai</span>
@endsection

@section('view_switcher')
    <a href="?{{ http_build_query(array_merge(request()->all(),['view'=>'list'])) }}"
       class="px-2 py-1 text-xs border-r {{ request('view','kanban')==='list' ? 'bg-[#714B67] text-white' : 'hover:bg-gray-50 text-gray-600' }}">☰ List</a>
    <a href="?{{ http_build_query(array_merge(request()->all(),['view'=>'kanban'])) }}"
       class="px-2 py-1 text-xs {{ request('view','kanban')==='kanban' ? 'bg-[#714B67] text-white' : 'hover:bg-gray-50 text-gray-600' }}">▦ Kanban</a>
@endsection

@section('control_panel_extra')
<div class="flex items-center gap-2">
    {{-- Active filter badges --}}
    @if($hasFilter)
    <div class="flex items-center gap-1.5 flex-wrap">
        @if(request('dept'))
        <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 text-[11px] font-semibold px-2 py-0.5 rounded-full border border-purple-200">
            🏢 {{ $departments->firstWhere('dept_id', request('dept'))?->name ?? request('dept') }}
        </span>
        @endif
        @if(request('status'))
        <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 text-[11px] font-semibold px-2 py-0.5 rounded-full border border-purple-200">
            {{ request('status') === 'Aktif' ? '🟢' : '🔴' }} {{ request('status') }}
        </span>
        @endif
        @if(request('search'))
        <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 text-[11px] font-semibold px-2 py-0.5 rounded-full border border-purple-200">
            🔍 "{{ request('search') }}"
        </span>
        @endif
        <a href="{{ route('employees.index') }}?view={{ request('view','kanban') }}"
           class="flex items-center gap-1 text-xs text-red-500 hover:text-red-700 border border-red-200 bg-red-50 rounded-lg px-2.5 py-1 transition hover:bg-red-100">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Reset
        </a>
    @endif
    </div>

    <button onclick="openEmpFilterModal()"
            class="ms-auto flex items-center gap-2 bg-[#714B67] hover:bg-[#5c3d56] text-white text-xs font-semibold px-4 py-1.5 rounded-lg shadow transition">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        Filter
        @if($hasFilter)
        <span class="bg-white text-[#714B67] text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center">
            {{ (request('dept') ? 1 : 0) + (request('status') ? 1 : 0) + (request('search') ? 1 : 0) }}
        </span>
        @endif
    </button>
</div>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">

    {{-- Department summary cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 mb-5">
        @foreach($departments as $dept)
        @php $cnt = $employees->where('department_id',$dept->dept_id)->count(); @endphp
        <button onclick="setDeptFilter('{{ $dept->dept_id }}')"
           class="bg-white rounded-xl border p-3 text-center hover:shadow-sm transition cursor-pointer
                  {{ request('dept')===$dept->dept_id ? 'border-[#714B67] bg-purple-50':'border-gray-200' }}">
            <div class="text-lg font-bold text-[#714B67]">{{ $cnt }}</div>
            <div class="text-xs text-gray-500 font-medium">{{ $dept->name }}</div>
            <div class="text-[10px] text-gray-400">pegawai</div>
        </button>
        @endforeach
    </div>

    <div class="flex items-center gap-4 mb-4 text-xs text-gray-500">
        <span>Total: <strong class="text-gray-800">{{ $totalCnt }}</strong></span>·
        <span>Aktif: <strong class="text-green-700">{{ $activeCnt }}</strong></span>·
        <span>Nonaktif: <strong class="text-red-600">{{ $totalCnt - $activeCnt }}</strong></span>
    </div>

    @if(request('view','kanban')==='kanban')
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
        @forelse($employees as $emp)
        <a href="{{ route('employees.show',$emp->id) }}" class="bg-white rounded-xl border hover:shadow-md transition {{ $emp->status==='Aktif' ? 'border-gray-200':'border-red-200 opacity-80' }}">
            <div class="p-4">
                <div class="flex items-start gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0 {{ $emp->status==='Aktif' ? 'bg-[#714B67]':'bg-gray-400' }}">
                        {{ strtoupper(substr($emp->name,0,1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-gray-800 truncate hover:text-[#714B67]">{{ $emp->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $emp->job_title }}</p>
                    </div>
                </div>
                <div class="space-y-1 text-xs text-gray-500">
                    <div>🏢 {{ $emp->department?->name ?? $emp->department_id }}</div>
                    <div>📍 {{ $emp->location }}</div>
                    <div>🆔 <span class="font-mono">{{ $emp->id }}</span></div>
                </div>
            </div>
            <div class="border-t px-4 py-2 flex items-center justify-between {{ $emp->status==='Aktif' ? 'bg-green-50':'bg-red-50' }}">
                <span class="flex items-center gap-1 text-xs font-medium {{ $emp->status==='Aktif' ? 'text-green-700':'text-red-600' }}">
                    <span class="w-2 h-2 rounded-full inline-block {{ $emp->status==='Aktif' ? 'bg-green-500':'bg-red-400' }}"></span>
                    {{ $emp->status }}
                </span>
                @if($emp->canSignOff()) <span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">✓ Sign-off</span> @endif
            </div>
        </a>
        @empty
        <div class="col-span-4 text-center py-12 text-gray-400 text-sm">Tidak ada pegawai ditemukan.</div>
        @endforelse
    </div>
    @else
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm">
        <table class="w-full text-xs">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">ID</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Nama</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Departemen</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Jabatan</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Lokasi</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Status</th>
                    <th class="text-left px-4 py-2 text-gray-500 font-medium">Sign-off</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr class="border-b last:border-0 hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('employees.show',$emp->id) }}'">
                    <td class="px-4 py-2 font-mono text-[#714B67] font-semibold">{{ $emp->id }}</td>
                    <td class="px-4 py-2 font-medium text-gray-800">{{ $emp->name }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $emp->department?->name ?? $emp->department_id }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $emp->job_title }}</td>
                    <td class="px-4 py-2 text-gray-500">{{ $emp->location }}</td>
                    <td class="px-4 py-2">
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full {{ $emp->status==='Aktif' ? 'bg-green-100 text-green-700':'bg-red-100 text-red-600' }}">
                            <span class="w-1.5 h-1.5 rounded-full inline-block {{ $emp->status==='Aktif' ? 'bg-green-500':'bg-red-400' }}"></span>
                            {{ $emp->status }}
                        </span>
                    </td>
                    <td class="px-4 py-2">{{ $emp->canSignOff() ? '✓ Ya' : '— Tidak' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-8 text-gray-400">Tidak ada pegawai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════
     FILTER MODAL — Employees
     ══════════════════════════════════════════ --}}
<div id="empFilterModal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     onclick="closeEmpFilterModalOutside(event)">

    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>

    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-modal">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-[#714B67] to-[#4a2e47] px-6 py-5 flex items-center justify-between">
            <div>
                <h2 class="text-white font-bold text-base flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
                    </svg>
                    Filter Pegawai
                </h2>
                <p class="text-white/60 text-xs mt-0.5">Sempitkan hasil berdasarkan kriteria berikut</p>
            </div>
            <button onclick="closeEmpFilterModal()"
                    class="w-8 h-8 flex items-center justify-center rounded-lg bg-white/20 hover:bg-white/30 text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Form --}}
        <form method="GET" action="{{ route('employees.index') }}" class="px-6 py-5 space-y-4">
            <input type="hidden" name="view" value="{{ request('view','kanban') }}">

            {{-- Cari nama --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                    🔍 Cari Nama Pegawai
                </label>
                <input type="text" name="search"
                       value="{{ request('search') }}"
                       placeholder="Contoh: Sari, Arif…"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#714B67]/30 focus:border-[#714B67] bg-gray-50 transition">
            </div>

            {{-- Departemen + Status --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        🏢 Departemen
                    </label>
                    <select name="dept"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#714B67]/30 focus:border-[#714B67] bg-gray-50 transition appearance-none">
                        <option value="">Semua Dept.</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->dept_id }}" {{ request('dept')===$dept->dept_id ? 'selected':'' }}>
                            {{ $dept->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">
                        ⚡ Status
                    </label>
                    <select name="status"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-[#714B67]/30 focus:border-[#714B67] bg-gray-50 transition appearance-none">
                        <option value="">Semua Status</option>
                        <option value="Aktif"    {{ request('status')==='Aktif'    ? 'selected':'' }}>🟢 Aktif</option>
                        <option value="Nonaktif" {{ request('status')==='Nonaktif' ? 'selected':'' }}>🔴 Nonaktif</option>
                    </select>
                </div>
            </div>

            {{-- Quick presets --}}
            <div>
                <label class="block text-xs font-semibold text-gray-400 mb-2 uppercase tracking-wide">Filter Cepat</label>
                <div class="flex items-center gap-2 flex-wrap">
                    @foreach($departments as $dept)
                    <button type="button" onclick="setEmpDept('{{ $dept->dept_id }}')"
                            class="text-[11px] px-3 py-1 rounded-full border border-gray-200 text-gray-600 hover:border-[#714B67] hover:text-[#714B67] hover:bg-purple-50 transition">
                        {{ $dept->name }}
                    </button>
                    @endforeach
                    <button type="button" onclick="setEmpStatus('Nonaktif')"
                            class="text-[11px] px-3 py-1 rounded-full border border-red-200 text-red-500 hover:bg-red-50 transition">
                        🔴 Nonaktif saja
                    </button>
                </div>
            </div>

            <div class="border-t border-gray-100"></div>

            {{-- Action buttons --}}
            <div class="flex items-center justify-between gap-3">
                <a href="{{ route('employees.index') }}?view={{ request('view','kanban') }}"
                   class="flex items-center gap-1.5 text-xs text-gray-500 hover:text-gray-700 font-medium transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset Filter
                </a>
                <div class="flex gap-2">
                    <button type="button" onclick="closeEmpFilterModal()"
                            class="px-4 py-2 text-xs font-semibold text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 text-xs font-bold text-white bg-gradient-to-r from-[#714B67] to-[#4a2e47] rounded-xl hover:opacity-90 transition shadow-sm flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<style>
.animate-modal {
    animation: modalIn 0.2s ease-out both;
}
@keyframes modalIn {
    from { opacity: 0; transform: scale(0.95) translateY(-8px); }
    to   { opacity: 1; transform: scale(1)    translateY(0);    }
}
</style>

<script>
function openEmpFilterModal() {
    const m = document.getElementById('empFilterModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeEmpFilterModal() {
    const m = document.getElementById('empFilterModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
    document.body.style.overflow = '';
}
function closeEmpFilterModalOutside(e) {
    if (e.target === document.getElementById('empFilterModal')) closeEmpFilterModal();
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeEmpFilterModal(); });

function setEmpDept(val) {
    document.querySelector('[name="dept"]').value = val;
}
function setEmpStatus(val) {
    document.querySelector('[name="status"]').value = val;
}
// Klik dept card dari grid langsung set filter & submit
function setDeptFilter(deptId) {
    const f = document.createElement('form');
    f.method = 'GET';
    f.action = '{{ route("employees.index") }}';
    const inputs = {
        dept: deptId,
        view: '{{ request("view","kanban") }}'
    };
    @if(request('status'))
    inputs.status = '{{ request("status") }}';
    @endif
    for (const [k, v] of Object.entries(inputs)) {
        const i = document.createElement('input');
        i.type = 'hidden'; i.name = k; i.value = v;
        f.appendChild(i);
    }
    document.body.appendChild(f);
    f.submit();
}
</script>
@endsection
