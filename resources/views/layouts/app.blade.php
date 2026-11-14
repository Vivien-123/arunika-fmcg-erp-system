<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Arunika ERP') — PT Arunika Distribusi Cepat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    purple: {
                        50: '#F7F3F6', 100: '#EDE4EA', 200: '#D8C4D1',
                        500: '#9B6B8F', 600: '#714B67', 700: '#5E3D56', 800: '#4A3044'
                    },
                    teal: {
                        50: '#E6F7F7', 100: '#B3E8E8', 500: '#018B91', 600: '#017A7F', 700: '#016A6E'
                    },
                },
                fontFamily: {
                    sans: ['Inter', 'system-ui', 'sans-serif'],
                    mono: ['"IBM Plex Mono"', 'ui-monospace', 'monospace'],
                }
            }
        }
    }
    </script>
    <style>
        body { background-color: #F8F9FA; color: #0F172A; }
        .tnum { font-variant-numeric: tabular-nums; }
        /* Sidebar */
        #sidebar { transition: width 180ms ease-out; }
        #sidebar.collapsed { width: 64px; overflow:hidden; }
        #sidebar.collapsed .sb-label,
        #sidebar.collapsed .sb-section-title,
        #sidebar.collapsed .sb-footer-label { display: none; }
        #sidebar.collapsed .sb-item { justify-content: center; padding-left: 0; padding-right: 0; }
        /* Smooth active border */
        .sb-item { border-left: 3px solid transparent; }
        .sb-item.active { border-left-color: #714B67; }
        /* Table */
        thead th { background: #F8F9FA; }
        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 3px; }
        /* Badge base */
        .badge { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:4px; font-size:11px; font-weight:600; }
        .badge-green  { background:#DCFCE7; color:#15803D; }
        .badge-yellow { background:#FEF9C3; color:#854D0E; }
        .badge-red    { background:#FEE2E2; color:#B91C1C; }
        .badge-blue   { background:#DBEAFE; color:#1D4ED8; }
        .badge-gray   { background:#F1F5F9; color:#475569; }
        .badge-purple { background:#F7F3F6; color:#714B67; }
        /* Primary button */
        .btn-primary { background:#714B67; color:white; border-radius:6px; padding:7px 16px; font-size:13px; font-weight:600; transition:background 150ms; }
        .btn-primary:hover { background:#5E3D56; }
        .btn-secondary { background:white; color:#374151; border:1px solid #E2E8F0; border-radius:6px; padding:6px 14px; font-size:13px; font-weight:500; transition:all 150ms; }
        .btn-secondary:hover { background:#F8F9FA; border-color:#CBD5E1; }
        .btn-teal { background:#018B91; color:white; border-radius:6px; padding:7px 16px; font-size:13px; font-weight:600; transition:background 150ms; }
        .btn-teal:hover { background:#017A7F; }
        /* Input */
        .form-input { border:1px solid #E2E8F0; border-radius:6px; padding:7px 12px; font-size:13px; color:#0F172A; background:white; transition:border-color 150ms; outline:none; }
        .form-input:focus { border-color:#714B67; box-shadow:0 0 0 2px rgba(113,75,103,0.12); }
        /* Panel — with subtle shadow */
        .panel { background:white; border:1px solid #E8ECF0; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04); }
        .panel:hover { box-shadow:0 2px 8px rgba(0,0,0,0.08); transition:box-shadow 150ms; }
        /* Alert banner */
        .alert-warning { background:#FFFBEB; border:1px solid #FDE68A; border-left:4px solid #F59E0B; border-radius:6px; padding:10px 14px; }
        .alert-info    { background:#EFF6FF; border:1px solid #BFDBFE; border-left:4px solid #3B82F6; border-radius:6px; padding:10px 14px; }
        .alert-danger  { background:#FEF2F2; border:1px solid #FECACA; border-left:4px solid #EF4444; border-radius:6px; padding:10px 14px; }
        .alert-success { background:#F0FDF4; border:1px solid #BBF7D0; border-left:4px solid #22C55E; border-radius:6px; padding:10px 14px; }
        /* Filter bar */
        .filter-input { border:1px solid #E2E8F0; border-radius:6px; padding:6px 12px; font-size:12px; background:white; color:#374151; outline:none; transition:border-color 150ms; }
        .filter-input:focus { border-color:#714B67; box-shadow:0 0 0 2px rgba(113,75,103,0.1); }
        /* Hover row */
        .tr-hover:hover { background:#FAFBFC; cursor:pointer; }
        /* Status dot */
        .dot { width:8px; height:8px; border-radius:50%; display:inline-block; flex-shrink:0; }
        /* Page layout helpers */
        .page-title { font-size:21px; font-weight:700; color:#0F172A; letter-spacing:-0.3px; }
        .page-sub   { font-size:12px; color:#94A3B8; margin-top:2px; }
        /* Table improvements */
        table { border-collapse:collapse; }
        thead th { font-size:11px; font-weight:600; letter-spacing:0.05em; }
        /* Active nav item in sidebar */
        .nav-active { color:#714B67 !important; background:#F7F3F6 !important; border-left-color:#714B67 !important; font-weight:600 !important; }
        /* Mono text */
        .mono { font-family:'IBM Plex Mono', ui-monospace, monospace; }
        /* Smooth transitions on interactive elements */
        a, button { transition: all 120ms ease; }
        /* Better focus rings */
        *:focus-visible { outline: 2px solid #714B67; outline-offset: 2px; }
    </style>
</head>
<body class="font-sans text-sm text-[#0F172A]">

{{-- ═══════════════════════════════════════════════════
     TOP NAVBAR — 48px fixed (sesuai Figma)
     ═══════════════════════════════════════════════════ --}}
<header class="bg-[#714B67] text-white fixed top-0 left-0 right-0 z-40 h-12 shadow-sm">
    <div class="flex items-center h-full px-4 gap-3">

        {{-- Logo / Company --}}
        <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0 hover:opacity-90 transition">
            <div class="w-7 h-7 bg-white/20 rounded flex items-center justify-center font-black text-sm">A</div>
            <span class="font-bold text-sm tracking-tight hidden sm:inline">ARUNIKA</span>
        </a>
        <span class="text-purple-200 text-xs hidden lg:inline">/</span>
        <span class="text-purple-200 text-xs hidden lg:inline">ERP</span>
        @if(isset($currentApp))
        <span class="text-purple-200 text-xs hidden lg:inline">/</span>
        <span class="text-white text-xs font-medium hidden lg:inline">{{ $currentApp }}</span>
        @endif

        {{-- Global Search --}}
        <div class="flex-1 max-w-sm mx-2 hidden md:block">
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" placeholder="Cari dokumen, SKU, kontak..."
                    class="w-full h-7 bg-[#5E3D56] text-white placeholder-purple-300 rounded text-xs px-3 pl-8 focus:outline-none focus:ring-1 focus:ring-purple-300">
            </div>
        </div>

        {{-- Right controls --}}
        <div class="ml-auto flex items-center gap-1 shrink-0">
            {{-- Bell --}}
            <button class="relative h-8 w-8 flex items-center justify-center rounded hover:bg-white/10 transition" title="Notifikasi">
                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="absolute top-0.5 right-0.5 w-4 h-4 bg-red-500 text-[9px] font-bold rounded-full flex items-center justify-center">3</span>
            </button>

            {{-- Help --}}
            <button class="h-8 w-8 flex items-center justify-center rounded hover:bg-white/10 transition text-purple-200 font-semibold text-sm">?</button>

            @auth
            {{-- Profile --}}
            <button onclick="toggleProfileMenu()" class="flex items-center gap-2 h-8 px-2 rounded hover:bg-white/10 transition ml-1">
                <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center text-[11px] font-bold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="text-left hidden lg:block">
                    <p class="text-xs font-semibold leading-tight">{{ Str::words(auth()->user()->name, 1, '') }}</p>
                    <p class="text-[10px] text-purple-200 leading-tight">{{ auth()->user()->role }}</p>
                </div>
            </button>

            {{-- Profile dropdown --}}
            <div id="profileMenu" class="hidden absolute right-3 top-11 bg-white text-gray-800 shadow-xl border border-gray-100 rounded-lg w-64 z-50 overflow-hidden">
                <div class="px-4 py-3 bg-purple-50 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400">{{ auth()->user()->email }}</p>
                    <span class="inline-block mt-1.5 text-[10px] font-mono font-semibold bg-purple-100 text-purple-700 px-2 py-0.5 rounded">{{ auth()->user()->role }}</span>
                </div>
                <div class="py-1 text-sm">
                    <a href="{{ route('employees.index') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-gray-600 hover:bg-gray-50 transition text-xs">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        Profil & Pegawai
                    </a>
                    <a href="{{ route('reporting.audit') }}" class="flex items-center gap-2.5 px-4 py-2.5 text-gray-600 hover:bg-gray-50 transition text-xs">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Audit Log
                    </a>
                </div>
                <div class="border-t border-gray-100 py-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-2.5 px-4 py-2.5 text-red-600 hover:bg-red-50 transition text-xs w-full text-left">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}" class="text-xs text-purple-200 hover:text-white underline">Login</a>
            @endauth
        </div>
    </div>
</header>

{{-- ═══════════════════════════════════════════════════
     APP BODY — Sidebar + Main
     ═══════════════════════════════════════════════════ --}}
<div class="flex pt-12 min-h-screen">

    @auth
    {{-- ── SIDEBAR: hanya muncul jika bukan home dan ada activeModule ── --}}
    @php
        $user        = auth()->user();
        $role        = $user->role;
        $currentPath = request()->path();
        $isHomePage  = $currentPath === 'home' || $currentPath === '/';

        // Detect active top-level module from URL
        $moduleFromPath = null;
        if (str_starts_with($currentPath, 'sales'))       $moduleFromPath = 'Sales';
        elseif (str_starts_with($currentPath, 'crm'))     $moduleFromPath = 'Sales'; // CRM under Sales sidebar
        elseif (str_starts_with($currentPath, 'purchase'))$moduleFromPath = 'Purchase';
        elseif (str_starts_with($currentPath, 'inventory'))$moduleFromPath = 'Inventory';
        elseif (str_starts_with($currentPath, 'accounting'))$moduleFromPath = 'Accounting';
        elseif (str_starts_with($currentPath, 'employees'))$moduleFromPath = 'Employees';
        elseif (str_starts_with($currentPath, 'reporting'))$moduleFromPath = 'Reporting';
        elseif (str_starts_with($currentPath, 'admin'))   $moduleFromPath = 'Admin';

        $showSidebar = !$isHomePage && $moduleFromPath !== null;

        // Per-module sidebar items (clean, only relevant sub-pages)
        $moduleSidebarItems = [
            'Sales' => [
                'title' => 'Sales & CRM',
                'items' => [
                    ['label'=>'Sales Orders',   'url'=>route('sales.orders.index'),  'match'=>'sales/orders'],
                    ['label'=>'Buat Quotation', 'url'=>route('sales.orders.create'), 'match'=>'sales/orders/create'],
                    ['label'=>'CRM Pipeline',   'url'=>route('crm.leads.index'),     'match'=>'crm/leads'],
                ],
            ],
            'Purchase' => [
                'title' => 'Purchase',
                'items' => [
                    ['label'=>'Purchase Orders', 'url'=>route('purchase.orders.index'),  'match'=>'purchase/orders'],
                    ['label'=>'Buat RFQ',        'url'=>route('purchase.orders.create'), 'match'=>'purchase/orders/create'],
                ],
            ],
            'Inventory' => [
                'title' => 'Inventory & Stock',
                'items' => [
                    ['label'=>'Stock Overview',    'url'=>route('inventory.stock'),             'match'=>'inventory/stock'],
                    ['label'=>'Purchase Orders',   'url'=>route('purchase.orders.index'),       'match'=>'purchase/orders'],
                    ['label'=>'Delivery Orders',   'url'=>route('inventory.deliveries.index'),  'match'=>'inventory/deliveries'],
                    ['label'=>'Goods Receipts',    'url'=>route('inventory.receipts.index'),    'match'=>'inventory/receipts'],
                    ['label'=>'Internal Transfer', 'url'=>route('inventory.transfer'),          'match'=>'inventory/transfer'],
                ],
            ],
            'Accounting' => [
                'title' => 'Accounting / Invoicing',
                'items' => [
                    ['label'=>'Customer Invoices', 'url'=>route('accounting.invoices.index'), 'match'=>'accounting/invoices'],
                    ['label'=>'Vendor Bills',      'url'=>route('accounting.bills.index'),    'match'=>'accounting/bills'],
                    ['label'=>'General Ledger',    'url'=>route('accounting.ledger.index'),   'match'=>'accounting/ledger'],
                    ['label'=>'Chart of Accounts', 'url'=>route('accounting.coa.index'),      'match'=>'accounting/coa'],
                ],
            ],
            'Employees' => [
                'title' => 'Employees',
                'items' => [
                    ['label'=>'Employee Directory', 'url'=>route('employees.index'),   'match'=>'employees'],
                    ['label'=>'Defense Prep',       'url'=>route('employees.defense'), 'match'=>'employees/defense'],
                ],
            ],
            'Reporting' => [
                'title' => 'Reporting',
                'items' => [
                    ['label'=>'Operational Dashboard',   'url'=>route('reporting.dashboard'),    'match'=>'reporting/dashboard'],
                    ['label'=>'Audit Trail',              'url'=>route('reporting.audit'),        'match'=>'reporting/audit'],
                    ['label'=>'Integration Testing',      'url'=>route('reporting.testing'),      'match'=>'reporting/testing'],
                    ['label'=>'Test Results M3',          'url'=>route('reporting.test-results'), 'match'=>'reporting/test-results'],
                ],
            ],
            'Admin' => [
                'title' => 'Master Admin',
                'items' => [
                    ['label'=>'Business Rules (SR)', 'url'=>route('admin.business-rules'), 'match'=>'admin/business-rules'],
                ],
            ],
        ];

        // Owner is strictly view-only: remove create/action options from sidebar
        if ($user->isOwner()) {
            foreach ($moduleSidebarItems as $k => &$mod) {
                $mod['items'] = array_values(array_filter($mod['items'], function($it) {
                    return !in_array($it['label'], ['Buat Quotation', 'Buat RFQ', 'Internal Transfer']);
                }));
            }
            unset($mod);
        }
    @endphp

    @if($showSidebar && isset($moduleSidebarItems[$moduleFromPath]))
    @php $sidebar = $moduleSidebarItems[$moduleFromPath]; @endphp
    <aside id="sidebar"
           class="w-60 bg-white border-r border-gray-200 flex-shrink-0 hidden md:flex flex-col sticky top-12"
           style="height:calc(100vh - 48px); overflow-y:auto;">

        {{-- Section header --}}
        <div class="px-4 py-3.5 border-b border-gray-100 shrink-0">
            <div class="flex items-center justify-between">
                <p class="text-xs font-bold text-gray-700">{{ $sidebar['title'] }}</p>
                <a href="{{ route('home') }}" class="text-gray-300 hover:text-gray-500 transition" title="Kembali ke Dashboard">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </a>
            </div>
        </div>

        {{-- Nav items --}}
        <nav class="py-2 flex-1">
            @foreach($sidebar['items'] as $item)
            @php $isActive = str_starts_with($currentPath, $item['match']); @endphp
            <a href="{{ $item['url'] }}"
               class="flex items-center gap-2.5 px-4 py-2 text-[13px] transition-colors
                      {{ $isActive
                          ? 'text-[#714B67] font-semibold bg-[#F7F3F6] border-l-[3px] border-[#714B67]'
                          : 'text-[#475569] hover:bg-gray-50 hover:text-gray-800 border-l-[3px] border-transparent' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-[#714B67]' : 'bg-gray-300' }} shrink-0"></span>
                {{ $item['label'] }}
            </a>
            @endforeach
        </nav>

        {{-- Back button --}}
        <div class="border-t border-gray-100 px-4 py-3 shrink-0">
            <a href="{{ route('home') }}" class="flex items-center gap-1.5 text-xs text-gray-400 hover:text-[#714B67] transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Semua Modul
            </a>
        </div>
    </aside>
    @endif
    @endauth

    {{-- ══ MAIN ══ --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Page header bar (breadcrumb + actions) --}}
        <div class="bg-white border-b border-gray-100 px-6 py-2 flex items-center gap-3 min-w-0 min-h-[40px]">
            <div class="flex items-center gap-1.5 text-xs text-gray-400 shrink-0">
                <a href="{{ route('home') }}" class="hover:text-[#714B67] transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </a>
                @yield('breadcrumb')
            </div>
            @hasSection('view_switcher')
            <div class="flex border border-gray-200 rounded-md overflow-hidden text-xs shrink-0">@yield('view_switcher')</div>
            @endif
            <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">@yield('control_panel_extra')</div>
        </div>

        {{-- Status bar --}}
        @hasSection('status_bar')
        <div class="bg-white border-b border-gray-100 px-6 py-2">@yield('status_bar')</div>
        @endif

        {{-- Smart buttons --}}
        @hasSection('smart_buttons')
        <div class="bg-white border-b border-gray-100 px-6 py-2 flex items-center gap-2 flex-wrap">@yield('smart_buttons')</div>
        @endif

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="mx-6 mt-4 alert-success text-xs text-green-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
        @endif
        @if(session('error') || session('warning'))
        <div class="mx-6 mt-4 alert-warning text-xs text-yellow-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-yellow-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            {{ session('error') ?? session('warning') }}
        </div>
        @endif
        @php $errBag = $errors ?? null; @endphp
        @if($errBag && $errBag->any())
        <div class="mx-6 mt-4 alert-danger text-xs text-red-800">
            <ul class="space-y-0.5">@foreach($errBag->all() as $e)<li class="flex items-start gap-1"><span class="mt-0.5 text-red-500">•</span>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        {{-- Page content --}}
        <main class="flex-1 overflow-auto w-full bg-[#F8F9FA]">
            <div class="max-w-screen-xl mx-auto px-6 py-6">
                @yield('content')
            </div>
        </main>

        @hasSection('chatter')
        <div class="bg-white border-t border-gray-200 px-6 py-4">@yield('chatter')</div>
        @endif
    </div>
</div>

<script>
function toggleProfileMenu(){
    const el = document.getElementById('profileMenu');
    if(el) el.classList.toggle('hidden');
}
document.addEventListener('click', function(e) {
    const pm = document.getElementById('profileMenu');
    if(pm && !e.target.closest('#profileMenu') && !e.target.closest('[onclick*="toggleProfileMenu"]')) {
        pm.classList.add('hidden');
    }
});
document.addEventListener('keydown', function(e) {
    if(e.key === 'Escape') {
        document.getElementById('profileMenu')?.classList.add('hidden');
    }
});
</script>
</body>
</html>
