<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PT Arunika ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/login-modern.css') }}">
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
<body class="login-page min-h-screen flex">

    {{-- ── LEFT PANEL: Branding (visual only) ── --}}
    <div class="login-brand-panel hidden lg:flex flex-col justify-center items-center w-[420px] bg-[#714B67] p-10 relative overflow-hidden shrink-0">

        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-[0.06]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 32px 32px;"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 bg-white/5 rounded-full"></div>
        <div class="absolute -top-20 -left-20 w-72 h-72 bg-white/5 rounded-full"></div>

        {{-- Logo only --}}
        <div class="relative z-10">
            <div class="w-20 h-20 bg-white/15 rounded-2xl flex items-center justify-center font-black text-white text-4xl">A</div>
            <p class="mt-5 text-center text-lg font-bold tracking-wide text-white">ARUNIKA ERP</p>
        </div>
    </div>

    {{-- ── RIGHT PANEL: Login form ── --}}
    <div class="login-content flex-1 flex items-center justify-center p-6 lg:p-12">
        <div class="login-card w-full max-w-sm">

            {{-- Mobile logo --}}
            <div class="flex items-center gap-2 mb-8 lg:hidden">
                <div class="w-8 h-8 bg-[#714B67] rounded-lg flex items-center justify-center font-black text-white">A</div>
                <span class="font-bold text-gray-800">ARUNIKA ERP</span>
            </div>

            {{-- Form header --}}
            <div class="mb-7 animate-fade-up">
                <h2 class="text-2xl font-bold text-gray-900">Masuk ke Arunika ERP</h2>
            </div>

            @if($errors->any())
            <div class="mb-5 bg-red-50 border border-red-200 border-l-4 border-l-red-500 rounded-lg px-4 py-3 animate-fade-up">
                <p class="text-red-700 text-sm font-medium">{{ $errors->first() }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <div class="animate-fade-up delay-1">
                    <label for="usernameInput" class="block text-xs font-semibold text-gray-600 mb-1.5">Username atau Email</label>
                    <input type="text" name="username" id="usernameInput" value="{{ old('username') }}" required autofocus autocomplete="username"
                           placeholder="Masukkan username atau email"
                           class="input-field">
                </div>

                <div class="animate-fade-up delay-2">
                    <label for="passwordInput" class="block text-xs font-semibold text-gray-600 mb-1.5">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="passwordInput" required autocomplete="current-password"
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
