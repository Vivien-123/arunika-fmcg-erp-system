@extends('layouts.app')
@section('title','Employees')
@php
    $currentApp  = 'Employees';
    $sidebarMenu = [
        ['label'=>'Semua Pegawai','url'=>route('employees.index'),'icon'=>'👥'],
        ['label'=>'Departemen',   'url'=>route('employees.index').'?view=dept','icon'=>'🏢'],
    ];
@endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-400">›</span>
    <a href="{{ route('employees.index') }}" class="hover:text-[#714B67]">Employees</a>
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700">Direktori Pegawai</span>
@endsection

@section('view_switcher')
    <a href="?{{ http_build_query(array_merge(request()->all(),['view'=>'list'])) }}" class="px-2 py-1 text-xs border-r {{ request('view','kanban')==='list' ? 'bg-[#714B67] text-white' : 'hover:bg-gray-50 text-gray-600' }}">☰ List</a>
    <a href="?{{ http_build_query(array_merge(request()->all(),['view'=>'kanban'])) }}" class="px-2 py-1 text-xs {{ request('view','kanban')==='kanban' ? 'bg-[#714B67] text-white' : 'hover:bg-gray-50 text-gray-600' }}">▦ Kanban</a>
@endsection

@section('control_panel_extra')
<form method="GET" class="flex items-center gap-2 text-xs">
    <input type="hidden" name="view" value="{{ request('view','kanban') }}">
    <select name="dept" onchange="this.form.submit()" class="border border-gray-300 rounded px-2 py-1 text-xs text-gray-600">
        <option value="">Semua Departemen</option>
        @foreach($departments as $dept)
            <option value="{{ $dept->dept_id }}" {{ request('dept')===$dept->dept_id ? 'selected' : '' }}>{{ $dept->name }}</option>
        @endforeach
    </select>
    <select name="status" onchange="this.form.submit()" class="border border-gray-300 rounded px-2 py-1 text-xs text-gray-600">
        <option value="">Semua Status</option>
        <option value="Aktif"    {{ request('status')==='Aktif'    ? 'selected':'' }}>Aktif</option>
        <option value="Nonaktif" {{ request('status')==='Nonaktif' ? 'selected':'' }}>Nonaktif</option>
    </select>
</form>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    {{-- Department cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 mb-5">
        @foreach($departments as $dept)
        @php $cnt = $employees->where('department_id',$dept->dept_id)->count(); @endphp
        <a href="?dept={{ $dept->dept_id }}&view={{ request('view','kanban') }}"
           class="bg-white rounded-lg border p-3 text-center hover:shadow-sm transition {{ request('dept')===$dept->dept_id ? 'border-[#714B67] bg-purple-50':'border-gray-200' }}">
            <div class="text-lg font-bold text-[#714B67]">{{ $cnt }}</div>
            <div class="text-xs text-gray-500 font-medium">{{ $dept->name }}</div>
            <div class="text-[10px] text-gray-400">pegawai</div>
        </a>
        @endforeach
    </div>

    <div class="flex items-center gap-4 mb-4 text-xs text-gray-500">
        <span>Total: <strong class="text-gray-800">{{ $totalCnt }}</strong></span>·
        <span>Aktif: <strong class="text-green-700">{{ $activeCnt }}</strong></span>·
        <span>Nonaktif: <strong class="text-red-600">{{ $totalCnt - $activeCnt }}</strong></span>
        @if(request('dept')) · <a href="?view={{ request('view','kanban') }}" class="text-[#714B67] underline">Hapus filter</a> @endif
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
@endsection
