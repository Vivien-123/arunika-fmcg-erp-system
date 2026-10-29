<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Arunika ERP') — PT Arunika Distribusi Cepat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root { --odoo-purple:#714B67; --odoo-teal:#00A09D; }
        .status-dot { width:8px;height:8px;border-radius:50%;display:inline-block; }
    </style>
</head>
<body class="bg-gray-100 text-sm font-sans">

{{-- TOP BAR --}}
<header class="bg-[#714B67] text-white shadow-md relative">
    <div class="flex items-center justify-between px-4 py-2">
        <div class="flex items-center gap-3">
            <button onclick="toggleAppSwitcher()" class="flex items-center gap-2 font-bold text-base hover:bg-[#5c3d56] px-2 py-1 rounded transition">
                <span class="text-lg">▦</span><span>Apps</span>
                @if(isset($currentApp))<span class="text-purple-200">·</span><span class="text-purple-200 font-normal">{{ $currentApp }}</span>@endif
            </button>
        </div>
        @auth
        <div class="flex items-center gap-3">
            <span class="bg-[#5c3d56] px-2 py-0.5 rounded text-xs font-mono">{{ auth()->user()->role }}</span>
            <span class="text-sm">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-xs bg-[#5c3d56] hover:bg-[#4a2e47] px-2 py-1 rounded transition">Keluar</button>
            </form>
        </div>
        @else
        <a href="{{ route('login') }}" class="text-sm underline">Login</a>
        @endauth
    </div>

    @auth
    <div id="appSwitcher" class="hidden absolute left-0 top-full bg-white text-gray-800 shadow-xl border-t-2 border-[#714B67] z-50 w-full px-4 py-3">
        <p class="text-xs text-gray-400 uppercase tracking-wide mb-2">Menu — {{ auth()->user()->role }}</p>
        <div class="grid grid-cols-4 gap-2 sm:grid-cols-6 md:grid-cols-8">
            @foreach(auth()->user()->allowedMenus() as $menu)
            @php
                $menuRoutes = ['Employees'=>route('employees.index'),'Wireframes'=>route('wireframes'),'Reporting'=>route('reporting.audit'),'Sales'=>'#','CRM'=>'#','Purchase'=>'#','Inventory'=>'#','Accounting'=>'#','Invoicing'=>'#'];
                $menuIcons  = ['Sales'=>'🛒','CRM'=>'📋','Purchase'=>'📦','Inventory'=>'🏭','Accounting'=>'📊','Invoicing'=>'🧾','Employees'=>'👤','Reporting'=>'📈','Wireframes'=>'🖼️'];
            @endphp
            <a href="{{ $menuRoutes[$menu] ?? '#' }}" onclick="toggleAppSwitcher()" class="flex flex-col items-center p-2 rounded hover:bg-gray-100 transition text-center">
                <span class="text-2xl">{{ $menuIcons[$menu] ?? '📌' }}</span>
                <span class="text-xs mt-1">{{ $menu }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endauth
</header>

{{-- LAYOUT --}}
<div class="flex min-h-screen">
    @auth
    <aside class="w-44 bg-white border-r border-gray-200 shadow-sm flex-shrink-0 hidden md:block">
        <nav class="py-2">
            @if(isset($sidebarMenu))
                @foreach($sidebarMenu as $item)
                <a href="{{ $item['url'] }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-50 {{ request()->is(ltrim($item['url'],'/')) ? 'text-[#714B67] font-semibold border-l-2 border-[#714B67] bg-purple-50' : 'text-gray-600' }}">
                    <span>{{ $item['icon'] ?? '' }}</span><span>{{ $item['label'] }}</span>
                </a>
                @endforeach
            @endif
        </nav>
    </aside>
    @endauth

    <div class="flex-1 flex flex-col overflow-hidden">
        {{-- Control Panel --}}
        <div class="bg-white border-b border-gray-200 px-4 py-2 flex items-center gap-2 flex-wrap">
            <div class="flex items-center gap-1 text-gray-500 text-xs">
                <a href="{{ route('home') }}" class="hover:text-[#714B67]">🏠</a>
                @yield('breadcrumb')
            </div>
            @hasSection('view_switcher')
            <div class="flex border border-gray-300 rounded overflow-hidden text-xs">@yield('view_switcher')</div>
            @endif
            @yield('control_panel_extra')
        </div>

        @hasSection('status_bar')
        <div class="bg-white border-b border-gray-100 px-4 py-1.5">@yield('status_bar')</div>
        @endif

        @hasSection('smart_buttons')
        <div class="bg-white border-b border-gray-100 px-4 py-1.5 flex items-center gap-2">@yield('smart_buttons')</div>
        @endif

        @if(session('success'))
        <div class="mx-4 mt-3 bg-green-50 border border-green-200 text-green-800 text-xs rounded px-3 py-2">✅ {{ session('success') }}</div>
        @endif
        @if(session('error'))
        <div class="mx-4 mt-3 bg-red-50 border border-red-200 text-red-800 text-xs rounded px-3 py-2">❌ {{ session('error') }}</div>
        @endif
        @if($errors->any())
        <div class="mx-4 mt-3 bg-red-50 border border-red-200 text-red-800 text-xs rounded px-3 py-2">
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <main class="flex-1 p-4 overflow-auto">@yield('content')</main>

        @hasSection('chatter')
        <div class="bg-white border-t border-gray-200 px-4 py-3">@yield('chatter')</div>
        @endif
    </div>
</div>

<script>
function toggleAppSwitcher(){const el=document.getElementById('appSwitcher');if(el)el.classList.toggle('hidden');}
document.addEventListener('click',function(e){const sw=document.getElementById('appSwitcher');if(sw&&!e.target.closest('header'))sw.classList.add('hidden');});
</script>
</body>
</html>
