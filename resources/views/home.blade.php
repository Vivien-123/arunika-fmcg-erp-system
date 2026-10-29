@extends('layouts.app')
@section('title','Beranda')
@php
    $currentApp  = 'Home';
    $sidebarMenu = [
        ['label'=>'Overview', 'url'=>route('home'), 'icon'=>'🏠'],
    ];
    $user  = auth()->user();
    $menus = $user->allowedMenus();

    $allMenus = [
        'Sales'      => ['icon'=>'🛒', 'desc'=>'Order to Cash',        'url'=>'#',                       'gradient'=>'from-orange-400 to-orange-500'],
        'CRM'        => ['icon'=>'📋', 'desc'=>'Lead & Opportunity',    'url'=>'#',                       'gradient'=>'from-yellow-400 to-amber-500'],
        'Purchase'   => ['icon'=>'📦', 'desc'=>'Procure to Pay',        'url'=>'#',                       'gradient'=>'from-blue-400 to-blue-600'],
        'Inventory'  => ['icon'=>'🏭', 'desc'=>'Stok & Transfer',       'url'=>'#',                       'gradient'=>'from-cyan-400 to-teal-500'],
        'Accounting' => ['icon'=>'📊', 'desc'=>'Jurnal & Ledger',       'url'=>'#',                       'gradient'=>'from-green-400 to-emerald-600'],
        'Invoicing'  => ['icon'=>'🧾', 'desc'=>'Invoice & Pembayaran',  'url'=>'#',                       'gradient'=>'from-emerald-400 to-green-600'],
        'Employees'  => ['icon'=>'👤', 'desc'=>'Pegawai & Departemen',  'url'=>route('employees.index'),  'gradient'=>'from-purple-400 to-violet-600'],
        'Reporting'  => ['icon'=>'📈', 'desc'=>'KPI & Audit Trail',     'url'=>route('reporting.audit'),  'gradient'=>'from-pink-400 to-rose-500'],
        'Wireframes' => ['icon'=>'🖼️', 'desc'=>'S-01 s/d S-18',        'url'=>route('wireframes'),       'gradient'=>'from-gray-400 to-gray-600'],
    ];

    // Hanya menu yang diizinkan saja
    $allowedMenus = array_filter($allMenus, fn($key) => in_array($key, $menus), ARRAY_FILTER_USE_KEY);
@endphp

@section('breadcrumb')
    <span class="mx-1 text-gray-400">›</span>
    <span class="text-gray-700 font-medium">Beranda</span>
@endsection

@section('content')
<div class="max-w-5xl mx-auto px-2">

    {{-- Hero welcome card --}}
    <div class="relative overflow-hidden bg-gradient-to-br from-[#714B67] to-[#4a2e47] rounded-2xl shadow-lg px-8 py-7 mb-8 flex items-center gap-6">
        {{-- Background decoration --}}
        <div class="absolute -right-10 -top-10 w-48 h-48 bg-white/5 rounded-full"></div>
        <div class="absolute -right-4 -bottom-8 w-32 h-32 bg-white/5 rounded-full"></div>

        <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center text-white font-black text-2xl shadow-inner flex-shrink-0">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div class="flex-1 z-10">
            <p class="text-white/70 text-xs font-medium uppercase tracking-widest mb-1">PT Arunika Distribusi Cepat</p>
            <h1 class="text-white font-bold text-xl leading-tight">Selamat datang, {{ $user->name }}</h1>
            <div class="flex items-center gap-3 mt-2">
                <span class="inline-flex items-center gap-1.5 bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 bg-green-400 rounded-full inline-block"></span>
                    {{ $user->role }}
                </span>
            </div>
        </div>
        <div class="z-10 text-right hidden sm:block">
            <p class="text-white/50 text-xs">Hari ini</p>
            <p class="text-white font-semibold text-sm">{{ now()->translatedFormat('d F Y') }}</p>
        </div>
    </div>

    {{-- Section title --}}
    <div class="mb-4">
        <h2 class="font-bold text-gray-800 text-base">Modul Anda</h2>
    </div>

    {{-- App grid — hanya yang diizinkan --}}
    @if(count($allowedMenus) === 0)
    <div class="bg-gray-50 border border-dashed border-gray-300 rounded-2xl py-16 text-center">
        <p class="text-4xl mb-3">🔒</p>
        <p class="text-gray-500 font-medium">Tidak ada modul yang tersedia untuk role Anda.</p>
        <p class="text-gray-400 text-xs mt-1">Hubungi administrator untuk mendapatkan akses.</p>
    </div>
    @else
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 mb-8">
        @foreach($allowedMenus as $name => $info)
        <a href="{{ $info['url'] }}"
           class="group relative overflow-hidden rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
            {{-- Gradient background --}}
            <div class="bg-gradient-to-br {{ $info['gradient'] }} p-5 flex flex-col items-center text-center">
                <div class="w-14 h-14 bg-white/20 rounded-xl flex items-center justify-center mb-3 group-hover:scale-110 transition-transform duration-300">
                    <span class="text-3xl">{{ $info['icon'] }}</span>
                </div>
                <span class="font-bold text-white text-sm">{{ $name }}</span>
                <span class="text-white/75 text-[11px] mt-0.5 leading-tight">{{ $info['desc'] }}</span>
            </div>
            {{-- Bottom strip --}}
            <div class="bg-white px-4 py-2 flex items-center justify-between">
                <span class="text-[11px] text-gray-500 font-medium">Buka →</span>
                <span class="w-2 h-2 rounded-full bg-green-400"></span>
            </div>
        </a>
        @endforeach
    </div>
    @endif

    {{-- Tabel ringkasan hak akses — hanya Owner yang boleh lihat (SR-15) --}}
    @if($user->role === 'Owner')
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/80">
            <div>
                <h3 class="font-semibold text-gray-700 text-sm">Ringkasan Hak Akses</h3>
                <p class="text-xs text-gray-400 mt-0.5">SR-15 · Hanya terlihat oleh Owner</p>
            </div>
            <span class="text-xs bg-green-100 text-green-700 font-semibold px-2.5 py-1 rounded-full">
                {{ count($allowedMenus) }}/{{ count($allMenus) }} diizinkan
            </span>
        </div>
        <div class="divide-y divide-gray-50">
            @foreach($allowedMenus as $name => $info)
            <div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50/60 transition">
                <div class="w-8 h-8 bg-gradient-to-br {{ $info['gradient'] }} rounded-lg flex items-center justify-center text-base flex-shrink-0">
                    {{ $info['icon'] }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800">{{ $name }}</p>
                    <p class="text-xs text-gray-400">{{ $info['desc'] }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 px-2.5 py-0.5 rounded-full border border-green-200">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        Aktif
                    </span>
                    <a href="{{ $info['url'] }}" class="text-[#714B67] hover:text-[#4a2e47] text-xs font-medium hover:underline">Buka →</a>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
