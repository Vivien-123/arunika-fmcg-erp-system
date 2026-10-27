@extends('layouts.app')
@section('title','Employee — '.$employee->name)
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
    <span class="text-gray-700">{{ $employee->name }}</span>
@endsection

@section('status_bar')
    <x-status-bar :stages="['Draft','Aktif','Nonaktif']" :current="$employee->status==='Aktif' ? 'Aktif' : 'Nonaktif'" />
@endsection

@section('smart_buttons')
    <x-smart-button icon="📋" label="Audit Log" :count="$auditCount" href="#log-notes" color="gray" />
    @if($employee->canSignOff())
        <x-smart-button icon="✅" label="Bisa Sign-off" href="#" color="green" />
    @else
        <x-smart-button icon="🚫" label="Tidak Sign-off" href="#" color="red" />
    @endif
    @if($isHR && $employee->privateRecord)
        <x-smart-button icon="🔐" label="Data Privat" href="#private" color="purple" />
    @endif
    <x-smart-button icon="🏢" label="{{ $employee->department?->name }}" href="{{ route('employees.index') }}?dept={{ $employee->department_id }}" color="blue" />
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-4">

    {{-- Employee Form Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full flex items-center justify-center text-white font-bold text-2xl flex-shrink-0 {{ $employee->status==='Aktif' ? 'bg-[#714B67]':'bg-gray-400' }}">
                {{ strtoupper(substr($employee->name,0,1)) }}
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-bold text-gray-800">{{ $employee->name }}</h2>
                <p class="text-sm text-gray-500">{{ $employee->job_title }}</p>
                <div class="mt-1 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full {{ $employee->status==='Aktif' ? 'bg-green-100 text-green-700':'bg-red-100 text-red-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full inline-block {{ $employee->status==='Aktif' ? 'bg-green-500':'bg-red-400' }}"></span>
                        {{ $employee->status }}
                    </span>
                    <span class="text-xs text-gray-400 font-mono">{{ $employee->id }}</span>
                </div>
            </div>
            @if($isHR)
            <form method="POST" action="{{ route('employees.toggleStatus',$employee->id) }}"
                  onsubmit="return confirm('Ubah status {{ $employee->name }}?')">
                @csrf
                <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border transition {{ $employee->status==='Aktif' ? 'border-red-300 text-red-600 hover:bg-red-50':'border-green-300 text-green-600 hover:bg-green-50' }}">
                    {{ $employee->status==='Aktif' ? '🔴 Nonaktifkan':'🟢 Aktifkan' }}
                </button>
            </form>
            @endif
        </div>

        <div class="px-6 py-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Employee ID</label><p class="font-mono font-semibold text-gray-800">{{ $employee->id }}</p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Nama Lengkap</label><p class="text-gray-800">{{ $employee->name }}</p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Departemen</label><p><a href="{{ route('employees.index') }}?dept={{ $employee->department_id }}" class="text-[#714B67] hover:underline">{{ $employee->department?->name ?? $employee->department_id }}</a></p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Jabatan</label><p class="text-gray-800">{{ $employee->job_title }}</p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Lokasi</label><p class="text-gray-800">{{ $employee->location }}</p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Status</label><p class="{{ $employee->status==='Aktif' ? 'text-green-700 font-semibold':'text-red-600 font-semibold' }}">{{ $employee->status }}</p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Mapped User</label>
                    <p class="text-gray-800">
                        @if($employee->mappedUser) <span class="font-mono">{{ $employee->mappedUser->username }}</span> <span class="text-xs text-gray-400">({{ $employee->mappedUser->role }})</span>
                        @else <span class="text-gray-400 italic">— Tidak ada</span> @endif
                    </p>
                </div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Otorisasi Sign-off</label>
                    @if($employee->canSignOff()) <p class="text-green-700 font-semibold text-sm">✅ Bisa sign-off gudang</p>
                    @else <p class="text-red-600 text-sm">🚫 Tidak bisa @if($employee->status==='Nonaktif')(Nonaktif)@elseif($employee->department_id!=='D-WH')(Bukan Warehouse)@else(Tidak ada user)@endif</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Private Record (SR-15, T-28) --}}
    @if($isHR)
    <div id="private" class="bg-white rounded-xl shadow-sm border border-purple-200 overflow-hidden">
        <div class="px-6 py-3 border-b border-purple-100 bg-purple-50 flex items-center gap-2">
            <span>🔐</span>
            <h3 class="text-sm font-semibold text-purple-700">Data Privat Pegawai</h3>
            <span class="text-xs text-purple-500 ml-auto">Hanya role HR (SR-15)</span>
        </div>
        <div class="px-6 py-4">
            @if($privateRecord)
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Email Pribadi</label><p>{{ $privateRecord->personal_email }}</p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Kontak Darurat</label><p>{{ $privateRecord->emergency_contact }}</p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Telepon Pribadi</label><p>{{ $privateRecord->private_phone }}</p></div>
                <div><label class="block text-xs text-gray-400 uppercase tracking-wide mb-0.5">Access Role</label><p class="text-purple-700 font-semibold">HR only</p></div>
            </div>
            @else
            <p class="text-sm text-gray-400 italic">Tidak ada data privat untuk pegawai ini.</p>
            @endif
        </div>
    </div>
    @else
    <div class="bg-gray-50 rounded-xl border border-gray-200 px-6 py-4 text-sm text-gray-500 flex items-center gap-2">
        <span>🔒</span>
        <span>Data pribadi hanya dapat diakses oleh role <strong>HR</strong>. (SR-15)</span>
    </div>
    @endif

    {{-- Related Docs --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4">
        <x-related-docs :docs="[]" />
        <p class="text-xs text-gray-400 italic mt-2">Related documents akan muncul setelah modul lain diimplementasi.</p>
    </div>

    {{-- Log Notes --}}
    <div id="log-notes" class="bg-white rounded-xl shadow-sm border border-gray-200 px-6 py-4">
        <x-log-notes :logs="$auditLogs" documentType="employee" documentId="{{ $employee->id }}" />
    </div>

</div>
@endsection
