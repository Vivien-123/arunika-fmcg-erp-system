<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Arunika ERP') — PT Arunika Distribusi Cepat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --purple-600:#714B67; --purple-700:#5E3D56; --purple-800:#493044;
            --purple-50:#F7F3F6; --purple-100:#EDE4EA; --purple-200:#D8C4D1;
            --teal-500:#018B91;
        }
        body { font-family: Inter, system-ui, sans-serif; }
        .tnum { font-variant-numeric: tabular-nums; }
        .mono { font-family: 'IBM Plex Mono', ui-monospace, monospace; }
        #sidebar { transition: width 200ms ease-out; }
        #sidebar.collapsed { width: 64px; }
        #sidebar.collapsed .sb-label, #sidebar.collapsed .sb-header { display:none; }
        #sidebar.collapsed .sb-item { justify-content:center; padding-left:0; padding-right:0; }
        .sticky-th { position:sticky; top:0; z-index:5; }
        .animate-drawer { transition: transform 240ms ease-out; }
    </style>
</head>
<body class="bg-gray-50 text-sm text-gray-800">

{{-- TOP NAVBAR (48px fixed) --}}
<header class="bg-[#714B67] text-white shadow-md fixed top-0 left-0 right-0 z-40 h-12">
    <div class="flex items-center justify-between h-full px-4">
        {{-- Left: App Switcher + Breadcrumb --}}
        <div class="flex items-center gap-3 min-w-0">
            <button onclick="toggleAppSwitcher()" class="flex items-center gap-2 font-bold text-base hover:bg-[#5E3D56] px-2 py-1 rounded transition shrink-0">
                <span class="text-lg">▦</span><span class="hidden sm:inline">Apps</span>
                @if(isset($currentApp))<span class="text-purple-200">·</span><span class="text-purple-200 font-normal text-sm hidden md:inline">{{ $currentApp }}</span>@endif
            </button>
            {{-- Breadcrumb in navbar --}}
            <div class="hidden lg:flex items-center gap-1 text-xs text-purple-200 min-w-0">
                <span>Arunika ERP</span>
                @yield('breadcrumb')
            </div>
        </div>

        {{-- Center: Global Search --}}
        <div class="flex-1 max-w-md mx-4 hidden md:block">
            <div class="relative">
                <input type="text" placeholder="Cari dokumen, pelanggan, produk, SKU..."
                    class="w-full h-8 bg-[#5E3D56] text-white placeholder-purple-200 rounded text-xs px-3 pl-8 focus:outline-none focus:ring-1 focus:ring-purple-300 transition-all">
                <svg class="absolute left-2.5 top-2 w-4 h-4 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        {{-- Right: Notifications + Profile --}}
        <div class="flex items-center gap-3 relative shrink-0">
            {{-- Notification Bell --}}
            <button class="relative hover:bg-[#5E3D56] p-2 rounded transition" title="Notifikasi">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">3</span>
            </button>

            {{-- Profile Dropdown --}}
            @auth
            <button onclick="toggleProfileMenu()" class="flex items-center gap-2 hover:bg-[#5E3D56] px-2 py-1 rounded transition">
                <div class="w-7 h-7 bg-[#5E3D56] rounded-full flex items-center justify-center text-xs font-bold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <span class="text-sm hidden lg:inline">{{ auth()->user()->name }}</span>
                <svg class="w-3 h-3 text-purple-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div id="profileMenu" class="hidden absolute right-0 top-full mt-1 bg-white text-gray-800 shadow-lg rounded-lg border border-gray-100 w-72 z-50">
                <div class="px-4 py-3 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-[#714B67] rounded-full flex items-center justify-center text-white text-sm font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-2.5 border-b border-gray-100">
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide mb-1">Role Aktif</p>
                    <span class="inline-flex items-center gap-1.5 bg-[#F7F3F6] text-[#714B67] text-xs mono font-semibold px-2.5 py-1 rounded">
                        <span class="w-1.5 h-1.5 bg-green-400 rounded-full"></span>
                        {{ auth()->user()->role }} User
                    </span>
                </div>
                <div class="px-4 py-2.5 border-b border-gray-100">
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide mb-1">Company</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-700 font-medium">PT Arunika Distribusi Cepat</span>
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <div class="py-1">
                    <a href="#" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"><span>👤</span> Profil Saya</a>
                    <a href="{{ route('reporting.audit') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-gray-600 hover:bg-gray-50 transition"><span>📋</span> Log Aktivitas</a>
                </div>
                <div class="border-t border-gray-100 py-1">
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 px-4 py-2 text-xs text-red-600 hover:bg-red-50 transition w-full text-left"><span>🚪</span> Keluar</button>
                    </form>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}" class="text-sm underline">Login</a>
            @endauth
        </div>
    </div>

    {{-- App Switcher panel --}}
    @auth
    <div id="appSwitcher" class="hidden absolute left-0 top-full bg-white text-gray-800 shadow-xl border-t-2 border-[#714B67] z-50 w-full px-4 py-3">
        <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Menu — {{ auth()->user()->role }}</p>
        <div class="grid grid-cols-4 gap-2 sm:grid-cols-6 md:grid-cols-8">
            @php
                $menus = auth()->user()->allowedMenus();
                $menuRoutes = [
                    'Sales'=>route('sales.orders.index'),'CRM'=>route('crm.leads.index'),
                    'Purchase'=>route('purchase.orders.index'),'Inventory'=>route('inventory.stock'),
                    'Accounting'=>route('accounting.invoices.index'),'Invoicing'=>route('accounting.invoices.index'),
                    'Employees'=>route('employees.index'),
                    'Reporting'=>route('reporting.dashboard'),'Wireframes'=>route('wireframes'),
                ];
                $menuIcons = ['Sales'=>'🛒','CRM'=>'📋','Purchase'=>'📦','Inventory'=>'🏭','Accounting'=>'📊','Invoicing'=>'🧾','Employees'=>'👤','Reporting'=>'📈','Wireframes'=>'🖼️'];
            @endphp
            @foreach($menus as $menu)
            <a href="{{ $menuRoutes[$menu] ?? '#' }}" onclick="toggleAppSwitcher()" class="flex flex-col items-center p-2 rounded hover:bg-gray-100 transition text-center">
                <span class="text-2xl">{{ $menuIcons[$menu] ?? '📌' }}</span>
                <span class="text-xs mt-1">{{ $menu }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endauth
</header>

{{-- BODY: Sidebar + Content --}}
<div class="flex pt-12 min-h-screen">

    {{-- LEFT SIDEBAR (240px expanded / 64px collapsed) --}}
    @auth
    @php
        $user = auth()->user(); $role = $user->role; $currentPath = request()->path();
        $sidebarSections = [
            'Sales & CRM' => [
                'roles' => ['Sales','Owner'],
                'items' => [
                    ['label'=>'Leads & Pipeline','url'=>route('crm.leads.index'),'icon'=>'📋'],
                    ['label'=>'Quotations','url'=>route('sales.orders.create'),'icon'=>'📄'],
                    ['label'=>'Sales Orders','url'=>route('sales.orders.index'),'icon'=>'🛒'],
                ],
            ],
            'Purchase' => [
                'roles' => ['Purchase','Owner'],
                'items' => [
                    ['label'=>'Purchase Orders','url'=>route('purchase.orders.index'),'icon'=>'📦'],
                    ['label'=>'Buat RFQ','url'=>route('purchase.orders.create'),'icon'=>'📝'],
                    ['label'=>'Goods Receipts','url'=>route('inventory.receipts.index'),'icon'=>'🚚'],
                ],
            ],
            'Inventory & Stock' => [
                'roles' => ['Purchase','Warehouse','Owner'],
                'items' => [
                    ['label'=>'Stock Overview','url'=>route('inventory.stock'),'icon'=>'📦'],
                    ['label'=>'Products & SKU','url'=>'#','icon'=>'🏭'],
                    ['label'=>'FEFO Lots','url'=>'#','icon'=>'🏷️'],
                    ['label'=>'Internal Transfer','url'=>route('inventory.transfer'),'icon'=>'🔁'],
                    ['label'=>'Delivery Orders','url'=>route('inventory.deliveries.index'),'icon'=>'📤'],
                    ['label'=>'Receipt & Quality','url'=>route('inventory.receipts.index'),'icon'=>'🚚'],
                ],
            ],
            'Accounting & Invoicing' => [
                'roles' => ['Finance','Owner'],
                'items' => [
                    ['label'=>'Customer Invoices','url'=>route('accounting.invoices.index'),'icon'=>'🧾'],
                    ['label'=>'Vendor Bills','url'=>route('accounting.bills.index'),'icon'=>'📑'],
                    ['label'=>'Register Payment','url'=>'#','icon'=>'💳'],
                    ['label'=>'General Ledger','url'=>route('accounting.ledger.index'),'icon'=>'📊'],
                    ['label'=>'Chart of Accounts','url'=>route('accounting.coa.index'),'icon'=>'📒'],
                ],
            ],
            'Employees' => [
                'roles' => ['HR','Owner'],
                'items' => [
                    ['label'=>'Employee Directory','url'=>route('employees.index'),'icon'=>'👤'],
                    ['label'=>'Departments','url'=>route('employees.index'),'icon'=>'🏢'],
                ],
            ],
            'Master Admin' => [
                'roles' => ['Owner','HR'],
                'items' => [
                    ['label'=>'Business Rules (SR)','url'=>route('admin.business-rules'),'icon'=>'⚙️'],
                    ['label'=>'Role Access Matrix','url'=>route('admin.business-rules'),'icon'=>'🔑'],
                ],
            ],
            'Reporting / Audit' => [
                'roles' => ['Finance','Warehouse','Owner'],
                'items' => [
                    ['label'=>'Operational Dashboard','url'=>route('reporting.dashboard'),'icon'=>'📈'],
                    ['label'=>'Audit Trail','url'=>route('reporting.audit'),'icon'=>'🔍'],
                    ['label'=>'Testing Monitor (T-01..T-36)','url'=>route('reporting.testing'),'icon'=>'🧪'],
                ],
            ],
        ];
    @endphp
    <aside id="sidebar" class="w-60 bg-white border-r border-gray-200 flex-shrink-0 hidden md:block self-start sticky top-12" style="max-height:calc(100vh - 48px);overflow-y:auto;">
        <div class="px-4 py-2 border-b border-gray-100">
            <button onclick="toggleSidebar()" class="flex items-center gap-2 text-xs text-gray-400 hover:text-gray-600 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <span class="sb-label">Collapse</span>
            </button>
        </div>
        <nav class="py-1">
            @foreach($sidebarSections as $sectionName => $section)
                @if(in_array($role, $section['roles']))
                <div class="sb-header px-4 pt-3 pb-1">
                    <p class="text-[10px] text-gray-400 uppercase font-bold tracking-wide">{{ $sectionName }}</p>
                </div>
                @foreach($section['items'] as $item)
                    @php $itemPath = ltrim(parse_url($item['url'], PHP_URL_PATH), '/'); $isActive = $itemPath !== '' && ($currentPath === $itemPath || str_starts_with($currentPath, $itemPath)); @endphp
                    <a href="{{ $item['url'] }}" class="sb-item flex items-center gap-2.5 px-4 py-2 text-sm transition
                        {{ $isActive ? 'text-[#714B67] font-semibold bg-[#F7F3F6] border-l-[3px] border-[#714B67]' : 'text-gray-600 hover:bg-gray-50 border-l-[3px] border-transparent' }}">
                        <span class="text-base shrink-0">{{ $item['icon'] }}</span>
                        <span class="sb-label">{{ $item['label'] }}</span>
                    </a>
                @endforeach
                @endif
            @endforeach
        </nav>
    </aside>
    @endauth

    {{-- MAIN CONTENT --}}
    <div class="flex-1 flex flex-col min-w-0">

        {{-- Control Panel --}}
        <div class="bg-white border-b border-gray-200 px-6 py-2 flex items-center gap-2 min-w-0">
            <div class="flex items-center gap-1 text-gray-500 text-xs shrink-0">
                <a href="{{ route('home') }}" class="hover:text-[#714B67]">🏠</a>
                @yield('breadcrumb')
            </div>
            @hasSection('view_switcher')
            <div class="flex border border-gray-300 rounded overflow-hidden text-xs shrink-0">@yield('view_switcher')</div>
            @endif
            <div class="flex items-center gap-2 flex-1 min-w-0 justify-end">@yield('control_panel_extra')</div>
        </div>

        @hasSection('status_bar')
        <div class="bg-white border-b border-gray-100 px-6 py-1.5">@yield('status_bar')</div>
        @endif

        @hasSection('smart_buttons')
        <div class="bg-white border-b border-gray-100 px-6 py-1.5 flex items-center gap-2 flex-wrap">@yield('smart_buttons')</div>
        @endif

        @if(session('success'))
        <div class="mx-6 mt-3 bg-green-50 border border-green-200 text-green-800 text-xs rounded px-3 py-2">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="mx-6 mt-3 bg-red-50 border border-red-200 text-red-800 text-xs rounded px-3 py-2">❌ {{ session('error') }}</div>
        @endif
        @php $errorsVar = $errors ?? null; @endphp
        @if($errorsVar && $errorsVar->any())
        <div class="mx-6 mt-3 bg-red-50 border border-red-200 text-red-800 text-xs rounded px-3 py-2">
            <ul class="list-disc list-inside">@foreach($errorsVar->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <main class="flex-1 p-6 overflow-auto w-full">@yield('content')</main>

        @hasSection('chatter')
        <div class="bg-white border-t border-gray-200 px-6 py-3">@yield('chatter')</div>
        @endif
    </div>
</div>

{{-- Audit Detail Side Drawer --}}
@hasSection('side_drawer')
<div id="auditDrawer" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closeDrawer()"></div>
    <div class="absolute right-0 top-0 bottom-0 w-[600px] max-w-full bg-white shadow-2xl animate-drawer overflow-y-auto" style="transform:translateX(0)">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
            <h2 class="text-base font-semibold text-gray-800">@yield('drawer_title')</h2>
            <button onclick="closeDrawer()" class="text-gray-400 hover:text-gray-600 p-1 rounded hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6">@yield('side_drawer')</div>
    </div>
</div>
@endif

<script>
function toggleAppSwitcher(){const el=document.getElementById('appSwitcher');if(el)el.classList.toggle('hidden');}
function toggleProfileMenu(){const el=document.getElementById('profileMenu');if(el)el.classList.toggle('hidden');}
function toggleSidebar(){const el=document.getElementById('sidebar');if(el)el.classList.toggle('collapsed');}
function closeDrawer(){const el=document.getElementById('auditDrawer');if(el)el.classList.add('hidden');}
function openDrawer(){const el=document.getElementById('auditDrawer');if(el)el.classList.remove('hidden');}
document.addEventListener('click',function(e){
    const sw=document.getElementById('appSwitcher');
    if(sw&&!e.target.closest('header'))sw.classList.add('hidden');
    const pm=document.getElementById('profileMenu');
    if(pm&&!e.target.closest('#profileMenu')&&!e.target.closest('[onclick*="toggleProfileMenu"]'))pm.classList.add('hidden');
});
</script>
</body>
</html>
