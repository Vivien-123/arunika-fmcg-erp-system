@extends('layouts.app')
@section('title','Profil Saya')
@php $currentApp = 'Profil Saya'; @endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700">Profil Saya</span>
@endsection

@section('content')
@php $user = auth()->user(); @endphp
<div class="max-w-2xl mx-auto space-y-4">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-[#714B67] flex items-center justify-center text-white font-bold text-2xl shrink-0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-lg font-bold text-gray-800 truncate">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->role }}</p>
                <span class="inline-flex items-center gap-1 mt-1 text-[11px] px-2 py-0.5 rounded-full bg-purple-50 text-[#714B67] font-semibold">
                    Akun portal
                </span>
            </div>
        </div>

        <div class="px-6 py-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <label class="block text-[10px] text-gray-400 uppercase tracking-wide mb-0.5">Username</label>
                    <p class="font-mono font-semibold text-gray-800">{{ $user->username }}</p>
                </div>
                <div>
                    <label class="block text-[10px] text-gray-400 uppercase tracking-wide mb-0.5">Nama Lengkap</label>
                    <p class="text-gray-800">{{ $user->name }}</p>
                </div>
                <div>
                    <label class="block text-[10px] text-gray-400 uppercase tracking-wide mb-0.5">Email</label>
                    <p class="text-gray-800 break-all">{{ $user->email }}</p>
                </div>
                <div>
                    <label class="block text-[10px] text-gray-400 uppercase tracking-wide mb-0.5">Role</label>
                    <p class="text-gray-800">{{ $user->role }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-gray-50 rounded-xl border border-gray-200 px-6 py-4 text-xs text-gray-500 flex items-start gap-2">
        <span class="text-gray-400">ℹ️</span>
        <span>Akun ini belum terhubung ke data pegawai. Hubungi HR bila profil pegawai perlu ditautkan.</span>
    </div>

</div>
@endsection
