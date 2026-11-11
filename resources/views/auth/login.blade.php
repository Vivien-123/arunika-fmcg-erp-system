<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PT Arunika ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .mono { font-family: 'IBM Plex Mono', monospace; }
        .input-field {
            width: 100%;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 10px 14px;
            font-size: 13px;
            color: #0F172A;
            background: white;
            outline: none;
            transition: border-color 150ms, box-shadow 150ms;
        }
        .input-field:focus {
            border-color: #714B67;
            box-shadow: 0 0 0 3px rgba(113,75,103,0.1);
        }
        .input-field::placeholder { color: #94A3B8; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-up { animation: fadeUp 0.4s ease-out both; }
        .delay-1 { animation-delay: 0.05s; }
        .delay-2 { animation-delay: 0.1s; }
        .delay-3 { animation-delay: 0.15s; }
    </style>
</head>
<body class="min-h-screen bg-[#F8F9FA] flex">

    {{-- ── LEFT PANEL: Branding ── --}}
    <div class="hidden lg:flex flex-col justify-between w-[420px] bg-[#714B67] p-10 relative overflow-hidden shrink-0">

        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 32px 32px;"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-white/5 rounded-full"></div>
        <div class="absolute -top-20 -left-20 w-72 h-72 bg-white/5 rounded-full"></div>

        {{-- Logo --}}
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-12">
                <div class="w-9 h-9 bg-white/20 rounded-lg flex items-center justify-center font-black text-white text-lg">A</div>
                <div>
                    <p class="text-white font-bold text-base tracking-tight">ARUNIKA</p>
                    <p class="text-purple-200 text-xs mono">ERP v2026</p>
                </div>
            </div>

            <h1 class="text-4xl font-extrabold text-white leading-tight mb-4">
                Distribusi cepat.<br>Operasi terkendali.
            </h1>
            <p class="text-purple-200 text-sm leading-relaxed">
                PT Arunika Distribusi Cepat — sistem ERP terintegrasi untuk distribusi FMCG Jabodetabek–Bandung.
            </p>
        </div>

        {{-- Stats --}}
        <div class="relative z-10">
            <div class="bg-white/10 rounded-xl p-5 mb-6">
                <div class="grid grid-cols-2 gap-4">
                    @foreach([['18','Layar sistem'],['22','Aturan bisnis'],['36','Skenario uji'],['02','Gudang']] as [$n,$l])
                    <div>
                        <p class="text-2xl font-bold text-white mono">{{ $n }}</p>
                        <p class="text-purple-200 text-xs mt-0.5">{{ $l }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                <p class="text-purple-200 text-xs">Odoo 17 Inspired · Desktop 1440×1024 · Sales / Inventory / Finance / People / Audit</p>
            </div>
        </div>
    </div>

    {{-- ── RIGHT PANEL: Login form ── --}}
    <div class="flex-1 flex items-center justify-center p-6 lg:p-12">
        <div class="w-full max-w-sm">

            {{-- Mobile logo --}}
            <div class="flex items-center gap-2 mb-8 lg:hidden">
                <div class="w-8 h-8 bg-[#714B67] rounded-lg flex items-center justify-center font-black text-white">A</div>
                <span class="font-bold text-gray-800">ARUNIKA ERP</span>
            </div>

            {{-- Form header --}}
            <div class="mb-7 animate-fade-up">
                <h2 class="text-2xl font-bold text-gray-900">Masuk ke akun Anda</h2>
                <p class="text-sm text-gray-400 mt-1">Gunakan username dan password yang diberikan admin.</p>
            </div>

            @if($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 border-l-4 border-l-red-500 rounded-lg px-4 py-3 animate-fade-up">
                <p class="text-red-700 text-sm font-medium">{{ $errors->first() }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <div class="animate-fade-up delay-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Username atau Email</label>
                    <input type="text" name="username" value="{{ old('username') }}" required autofocus
                           placeholder="Contoh: sales_user"
                           class="input-field">
                </div>

                <div class="animate-fade-up delay-2">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="passwordInput" required
                               placeholder="••••••••"
                               class="input-field pr-10">
                        <button type="button" onclick="togglePassword()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 transition">
                            <svg id="eyeOpen" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eyeClosed" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2 animate-fade-up delay-2">
                    <input type="checkbox" name="remember" id="remember" class="w-3.5 h-3.5 accent-[#714B67] rounded">
                    <label for="remember" class="text-xs text-gray-500 cursor-pointer">Ingat saya</label>
                </div>

                <div class="pt-1 animate-fade-up delay-3">
                    <button type="submit"
                            class="w-full bg-[#714B67] hover:bg-[#5E3D56] text-white font-semibold rounded-lg py-2.5 text-sm transition-all duration-150 active:scale-[.98]">
                        Masuk
                    </button>
                </div>
            </form>

            {{-- Credential table --}}
            <div class="mt-8 animate-fade-up delay-3">
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-200 flex items-center justify-between">
                        <p class="text-xs font-semibold text-gray-600">Akun Uji</p>
                        <span class="mono text-[11px] bg-purple-50 text-purple-700 px-2 py-0.5 rounded font-semibold">Demo2026!</span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @php
                        $accounts = [
                            ['sales_user',    'Sales',       'Buat SO, CRM, diskon ≤10%'],
                            ['warehouse_mgr', 'Warehouse',   'Delivery, Receipt, sign-off'],
                            ['finance_user',  'Finance',     'Invoice, Bill, Ledger, Payment'],
                            ['purchase_user', 'Purchase',    'PO, RFQ, Goods Receipt'],
                            ['hr_user',       'HR',          'Employees + data privat'],
                            ['owner_user',    'Owner',       'View-only semua modul'],
                            ['master_admin',  'MasterAdmin', 'Full edit semua modul'],
                        ];
                        @endphp
                        @foreach($accounts as [$u,$r,$desc])
                        <button type="button"
                                onclick="document.querySelector('[name=username]').value='{{ $u }}'; document.querySelector('[name=password]').value='Demo2026!';"
                                class="w-full flex items-center gap-3 px-4 py-2 hover:bg-purple-50/50 transition text-left group">
                            <div class="w-6 h-6 bg-[#714B67] rounded-full flex items-center justify-center text-white text-[10px] font-bold shrink-0">
                                {{ strtoupper(substr($u,0,1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-[11px] font-semibold mono text-gray-700 group-hover:text-[#714B67] transition">{{ $u }}</p>
                                <p class="text-[10px] text-gray-400 truncate">{{ $desc }}</p>
                            </div>
                            <span class="text-[10px] font-semibold
                                @if($r==='MasterAdmin') bg-indigo-100 text-indigo-700
                                @elseif($r==='Owner') bg-gray-100 text-gray-500
                                @elseif($r==='HR') bg-purple-100 text-purple-700
                                @elseif($r==='Finance') bg-green-100 text-green-700
                                @elseif($r==='Sales') bg-orange-100 text-orange-700
                                @elseif($r==='Warehouse') bg-cyan-100 text-cyan-700
                                @else bg-blue-100 text-blue-700
                                @endif
                                px-1.5 py-0.5 rounded shrink-0">{{ $r }}</span>
                        </button>
                        @endforeach
                    </div>
                    <div class="bg-gray-50 px-4 py-2 border-t border-gray-200">
                        <p class="text-[10px] text-gray-400">Klik baris untuk isi form otomatis · Owner = read-only</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const open  = document.getElementById('eyeOpen');
    const closed= document.getElementById('eyeClosed');
    if (input.type === 'password') {
        input.type = 'text';
        open.classList.add('hidden');
        closed.classList.remove('hidden');
    } else {
        input.type = 'password';
        open.classList.remove('hidden');
        closed.classList.add('hidden');
    }
}
</script>
</body>
</html>
