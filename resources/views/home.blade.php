@extends('layouts.app')
@section('title','Dashboard')
@php
    $currentApp = 'Dashboard';
    $user  = auth()->user();
    $menus = $user->allowedMenus();

    $allMenus = [
        'Sales'      => ['url'=>route('sales.orders.index'),        'desc'=>'Penawaran dan pesanan penjualan', 'icon'=>'🛍️', 'class'=>'module-sales'],
        'CRM'        => ['url'=>route('crm.leads.index'),           'desc'=>'Kelola prospek dan peluang penjualan', 'icon'=>'🤝', 'class'=>'module-crm'],
        'Purchase'   => ['url'=>route('purchase.orders.index'),     'desc'=>'Permintaan pembelian dan pesanan pemasok', 'icon'=>'🛒', 'class'=>'module-purchase'],
        'Inventory'  => ['url'=>route('inventory.stock'),           'desc'=>'Pantau stok dan pergerakan barang', 'icon'=>'📦', 'class'=>'module-inventory'],
        'Accounting' => ['url'=>route('accounting.invoices.index'), 'desc'=>'Faktur dan transaksi keuangan', 'icon'=>'💰', 'class'=>'module-accounting'],
        'Employees'  => ['url'=>route('employees.index'),           'desc'=>'Data pegawai dan departemen', 'icon'=>'👥', 'class'=>'module-employees'],
        'Reporting'  => ['url'=>route('reporting.dashboard'),       'desc'=>'Laporan operasional dan kinerja', 'icon'=>'📊', 'class'=>'module-reporting'],
        'Admin'      => ['url'=>route('admin.business-rules'),      'desc'=>'Aturan dan pengaturan aplikasi', 'icon'=>'⚙️', 'class'=>'module-admin'],
    ];
    $allowedMenus = array_filter($allMenus, fn($key) => in_array($key, $menus), ARRAY_FILTER_USE_KEY);
@endphp

@section('breadcrumb')
    <span class="mx-1">/</span>
    <span class="text-gray-600 font-medium">Dashboard</span>
@endsection

@section('content')
<div class="dashboard-shell">
    <section class="dashboard-heading">
        <div>
            <span class="dashboard-eyebrow">ARUNIKA ERP</span>
            <h1>Selamat datang, {{ Str::words($user->name, 2, '') }}</h1>
            <p>Pilih aplikasi untuk mulai mengelola pekerjaan Anda.</p>
        </div>
        <div class="dashboard-date">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                <rect x="3.5" y="5" width="17" height="15.5" rx="2" stroke="currentColor" stroke-width="1.6"/>
                <path d="M7.5 3.5v3M16.5 3.5v3M3.5 9.5h17" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            <span>{{ now()->format('d M Y') }}</span>
        </div>
    </section>

    @if(count($allowedMenus) === 0)
    <div class="panel px-6 py-12 text-center">
        <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        <p class="text-sm font-medium text-gray-500">Tidak ada modul yang tersedia.</p>
        <p class="text-xs text-gray-400 mt-1">Hubungi administrator untuk mendapatkan akses.</p>
    </div>
    @else
    <section class="dashboard-launcher" aria-labelledby="dashboardAppsTitle">
        <div class="dashboard-launcher-toolbar">
            <div>
                <h2 id="dashboardAppsTitle">Aplikasi</h2>
                <p id="dashboardAppCount" aria-live="polite">{{ count($allowedMenus) }} aplikasi tersedia</p>
            </div>
            <label class="dashboard-app-search">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none">
                    <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m16 16 4 4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <input id="dashboardAppSearch" type="search" placeholder="Cari aplikasi..." aria-label="Cari aplikasi" autocomplete="off">
            </label>
        </div>
        <div class="dashboard-module-grid">
        @foreach($allowedMenus as $name => $info)
        @php $moduleClass = $info['class'] ?? 'module-default'; @endphp
        <a href="{{ $info['url'] }}"
           class="dashboard-module-card {{ $moduleClass }}"
           data-app-card
           data-app-search="{{ strtolower($name.' '.$info['desc']) }}">
            <span class="dashboard-module-icon" aria-hidden="true">{{ $info['icon'] }}</span>
            <span class="dashboard-module-copy">
                <span class="dashboard-module-title">{{ $name }}</span>
                <span class="dashboard-module-description">{{ $info['desc'] }}</span>
            </span>
            <span class="dashboard-module-bottom">
                <span class="dashboard-module-open">Buka aplikasi</span>
                <span class="dashboard-module-arrow-small" aria-hidden="true">→</span>
            </span>
        </a>
        @endforeach
        </div>
        <p id="dashboardNoApps" class="dashboard-no-apps" hidden>Tidak ada aplikasi yang cocok dengan pencarian.</p>
    </section>
    <script>
        (() => {
            const search = document.getElementById('dashboardAppSearch');
            const cards = Array.from(document.querySelectorAll('[data-app-card]'));
            const count = document.getElementById('dashboardAppCount');
            const empty = document.getElementById('dashboardNoApps');

            if (!search || !count || !empty) return;

            search.addEventListener('input', () => {
                const query = search.value.trim().toLowerCase();
                let visible = 0;

                cards.forEach((card) => {
                    const matches = card.dataset.appSearch.includes(query);
                    card.hidden = !matches;
                    if (matches) visible += 1;
                });

                count.textContent = `${visible} aplikasi tersedia`;
                empty.hidden = visible !== 0;
            });
        })();
    </script>
    @endif

</div>
@endsection
