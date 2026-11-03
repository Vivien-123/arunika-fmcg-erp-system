@extends('layouts.app')
@section('title','Employees')
@php
    $currentApp  = 'Employees';
    $sidebarMenu = [
        ['label'=>'Semua Pegawai','url'=>route('employees.index'),'icon'=>'👥'],
        ['label'=>'Departemen',   'url'=>route('employees.index').'?view=dept','icon'=>'🏢'],
    ];
    $hasFilter   = request()->hasAny(['dept','status','search']);
    $filterCount = (request('dept') ? 1:0) + (request('status') ? 1:0) + (request('search') ? 1:0);
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
{{-- Filter badges --}}
@if($hasFilter)
    @if(request('dept'))
    <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 text-[11px] font-semibold px-2 py-0.5 rounded-full border border-purple-200">
        🏢 {{ $departments->firstWhere('dept_id', request('dept'))?->name ?? request('dept') }}
    </span>
    @endif
    @if(request('status'))
    <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 text-[11px] font-semibold px-2 py-0.5 rounded-full border border-purple-200">
        {{ request('status')==='Aktif' ? '🟢':'🔴' }} {{ request('status') }}
    </span>
    @endif
    @if(request('search'))
    <span class="inline-flex items-center gap-1 bg-purple-100 text-purple-700 text-[11px] font-semibold px-2 py-0.5 rounded-full border border-purple-200">
        🔍 "{{ request('search') }}"
    </span>
    @endif
    <a href="{{ route('employees.index') }}?view={{ request('view','kanban') }}"
       class="text-[11px] text-red-500 border border-red-200 bg-red-50 rounded px-2 py-0.5 hover:bg-red-100 transition">✕ Reset</a>
@endif
{{-- Spacer --}}
<span class="flex-1"></span>
{{-- Filter button --}}
<button onclick="openEmpFilterModal()"
        class="flex items-center gap-1.5 bg-[#714B67] hover:bg-[#5c3d56] text-white text-xs font-semibold px-3 py-1.5 rounded-lg shadow transition">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
    </svg>
    Filter
    @if($filterCount > 0)
    <span class="bg-white text-[#714B67] text-[10px] font-black w-4 h-4 rounded-full flex items-center justify-center leading-none">{{ $filterCount }}</span>
    @endif
</button>
@endsection

@section('content')

{{-- Department cards --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 mb-4">
    @foreach($departments as $dept)
    @php $cnt = $employees->where('department_id',$dept->dept_id)->count(); @endphp
    <button onclick="quickDeptFilter('{{ $dept->dept_id }}')"
            class="bg-white rounded-xl border p-3 text-center hover:shadow-sm transition
                   {{ request('dept')===$dept->dept_id ? 'border-[#714B67] bg-purple-50' : 'border-gray-200' }}">
        <div class="text-lg font-bold text-[#714B67]">{{ $cnt }}</div>
        <div class="text-xs text-gray-600 font-medium">{{ $dept->name }}</div>
        <div class="text-[10px] text-gray-400">pegawai</div>
    </button>
    @endforeach
</div>

{{-- Stats bar --}}
<div class="flex items-center gap-3 mb-4 text-xs text-gray-500">
    <span>Total: <strong class="text-gray-800">{{ $totalCnt }}</strong></span>
    <span class="text-gray-300">·</span>
    <span>Aktif: <strong class="text-green-600">{{ $activeCnt }}</strong></span>
    <span class="text-gray-300">·</span>
    <span>Nonaktif: <strong class="text-red-500">{{ $totalCnt - $activeCnt }}</strong></span>
</div>

{{-- Kanban / List view --}}
@if(request('view','kanban')==='kanban')
<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3">
    @forelse($employees as $emp)
    <a href="{{ route('employees.show',$emp->id) }}"
       class="bg-white rounded-xl border hover:shadow-md transition {{ $emp->status==='Aktif' ? 'border-gray-200' : 'border-red-200 opacity-80' }}">
        <div class="p-4">
            <div class="flex items-start gap-3 mb-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0
                            {{ $emp->status==='Aktif' ? 'bg-[#714B67]' : 'bg-gray-400' }}">
                    {{ strtoupper(substr($emp->name,0,1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm text-gray-800 truncate">{{ $emp->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $emp->job_title }}</p>
                </div>
            </div>
            <div class="space-y-1 text-xs text-gray-500">
                <div class="flex items-center gap-1">🏢 <span class="truncate">{{ $emp->department?->name ?? $emp->department_id }}</span></div>
                <div class="flex items-center gap-1">📍 {{ $emp->location }}</div>
                <div class="flex items-center gap-1">🆔 <span class="font-mono">{{ $emp->id }}</span></div>
            </div>
        </div>
        <div class="border-t px-4 py-2 flex items-center justify-between
                    {{ $emp->status==='Aktif' ? 'bg-green-50' : 'bg-red-50' }}">
            <span class="flex items-center gap-1 text-xs font-medium {{ $emp->status==='Aktif' ? 'text-green-700' : 'text-red-600' }}">
                <span class="w-2 h-2 rounded-full {{ $emp->status==='Aktif' ? 'bg-green-500' : 'bg-red-400' }}"></span>
                {{ $emp->status }}
            </span>
            @if($emp->canSignOff())
            <span class="text-[10px] bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">✓ Sign-off</span>
            @endif
        </div>
    </a>
    @empty
    <div class="col-span-5 text-center py-16 text-gray-400">Tidak ada pegawai ditemukan.</div>
    @endforelse
</div>

@else
{{-- List view --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
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
            <tr class="border-b last:border-0 hover:bg-gray-50 cursor-pointer"
                onclick="window.location='{{ route('employees.show',$emp->id) }}'">
                <td class="px-4 py-2 font-mono text-[#714B67] font-semibold">{{ $emp->id }}</td>
                <td class="px-4 py-2 font-medium text-gray-800">{{ $emp->name }}</td>
                <td class="px-4 py-2 text-gray-600">{{ $emp->department?->name ?? $emp->department_id }}</td>
                <td class="px-4 py-2 text-gray-600">{{ $emp->job_title }}</td>
                <td class="px-4 py-2 text-gray-500">{{ $emp->location }}</td>
                <td class="px-4 py-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs
                                 {{ $emp->status==='Aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $emp->status==='Aktif' ? 'bg-green-500' : 'bg-red-400' }}"></span>
                        {{ $emp->status }}
                    </span>
                </td>
                <td class="px-4 py-2 text-gray-500">{{ $emp->canSignOff() ? '✓ Ya' : '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="text-center py-8 text-gray-400">Tidak ada pegawai.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endif

{{-- ══ FILTER MODAL ══ --}}
<div id="empFilterModal"
     class="fixed inset-0 z-50 hidden items-center justify-center p-4"
     onclick="if(event.target===this)closeEmpFilterModal()">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md" style="animation:modalIn .2s ease-out">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-[#714B67] to-[#4a2e47] px-6 py-4 rounded-t-2xl flex items-center justify-between">
            <div>
                <h2 class="text-white font-bold text-sm flex items-center gap-2">
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

            {{-- Search --}}
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">🔍 Cari Nama Pegawai</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Contoh: Sari, Arif…"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#714B67]/30 focus:border-[#714B67] bg-gray-50">
            </div>

            {{-- Dept + Status --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">🏢 Departemen</label>
                    <select name="dept"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#714B67]/30 focus:border-[#714B67] bg-gray-50 appearance-none">
                        <option value="">Semua Dept.</option>
                        @foreach($departments as $d)
                        <option value="{{ $d->dept_id }}" {{ request('dept')===$d->dept_id ? 'selected' : '' }}>{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">⚡ Status</label>
                    <select name="status"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-xs focus:outline-none focus:ring-2 focus:ring-[#714B67]/30 focus:border-[#714B67] bg-gray-50 appearance-none">
                        <option value="">Semua Status</option>
                        <option value="Aktif"    {{ request('status')==='Aktif'    ? 'selected':'' }}>🟢 Aktif</option>
                        <option value="Nonaktif" {{ request('status')==='Nonaktif' ? 'selected':'' }}>🔴 Nonaktif</option>
                    </select>
                </div>
            </div>

            {{-- Quick presets --}}
            <div>
                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide mb-2">Filter Cepat</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($departments as $d)
                    <button type="button" onclick="document.querySelector('[name=dept]').value='{{ $d->dept_id }}'"
                            class="text-[11px] px-3 py-1 rounded-full border border-gray-200 text-gray-600 hover:border-[#714B67] hover:text-[#714B67] hover:bg-purple-50 transition">
                        {{ $d->name }}
                    </button>
                    @endforeach
                    <button type="button" onclick="document.querySelector('[name=status]').value='Nonaktif'"
                            class="text-[11px] px-3 py-1 rounded-full border border-red-200 text-red-500 hover:bg-red-50 transition">
                        🔴 Nonaktif saja
                    </button>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-4 flex items-center justify-between">
                <a href="{{ route('employees.index') }}?view={{ request('view','kanban') }}"
                   class="text-xs text-gray-500 hover:text-gray-700 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset Filter
                </a>
                <div class="flex gap-2">
                    <button type="button" onclick="closeEmpFilterModal()"
                            class="px-4 py-2 text-xs font-semibold text-gray-600 border border-gray-200 rounded-xl hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-5 py-2 text-xs font-bold text-white bg-gradient-to-r from-[#714B67] to-[#4a2e47] rounded-xl hover:opacity-90 shadow-sm flex items-center gap-1.5">
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
@keyframes modalIn {
    from { opacity:0; transform:scale(.95) translateY(-6px); }
    to   { opacity:1; transform:scale(1)   translateY(0);    }
}
</style>
<script>
function openEmpFilterModal(){
    const m=document.getElementById('empFilterModal');
    m.classList.remove('hidden'); m.classList.add('flex');
    document.body.style.overflow='hidden';
}
function closeEmpFilterModal(){
    const m=document.getElementById('empFilterModal');
    m.classList.add('hidden'); m.classList.remove('flex');
    document.body.style.overflow='';
}
document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeEmpFilterModal(); });

function quickDeptFilter(deptId){
    const url=new URL(window.location.href);
    url.searchParams.set('dept', deptId);
    url.searchParams.set('view', '{{ request("view","kanban") }}');
    window.location.href = url.toString();
}
</script>
@endsection
