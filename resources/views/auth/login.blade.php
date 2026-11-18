<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — PT Arunika ERP</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login-modern.css') }}">
</head>
<body class="login-page">
    <main class="login-shell">
        <div class="login-brand">
            <span class="login-brand-mark" aria-hidden="true">A</span>
            <span class="login-brand-copy">
                <strong>ARUNIKA ERP</strong>
                <small>PT Arunika Distribusi Cepat</small>
            </span>
        </div>

        <section class="login-card" aria-labelledby="loginTitle">
            <header class="login-intro">
                <span class="login-eyebrow">RUANG KERJA DIGITAL</span>
                <h1 id="loginTitle">Selamat datang kembali</h1>
                <p>Masuk untuk melanjutkan pekerjaan Anda.</p>
            </header>

            @if($errors->any())
            <div class="login-error" role="alert">
                <svg aria-hidden="true" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-11a1 1 0 112 0v4a1 1 0 11-2 0V7zm1 8a1.1 1.1 0 100-2.2A1.1 1.1 0 0010 15z" clip-rule="evenodd"/>
                </svg>
                <p>{{ $errors->first() }}</p>
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="login-form">
                @csrf

                <div class="login-field">
                    <label for="usernameInput">Username atau email</label>
                    <input type="text" name="username" id="usernameInput" value="{{ old('username') }}" required autofocus autocomplete="username"
                           placeholder="Masukkan username atau email">
                </div>

                <div class="login-field">
                    <label for="passwordInput">Password</label>
                    <div class="password-field">
                        <input type="password" name="password" id="passwordInput" required autocomplete="current-password"
                               placeholder="Masukkan password">
                        <button type="button" class="password-toggle" id="passwordToggle" aria-label="Tampilkan password" aria-pressed="false">
                            <svg id="eyeOpen" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                <path d="M2.5 12s3.4-6 9.5-6 9.5 6 9.5 6-3.4 6-9.5 6-9.5-6-9.5-6Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                <circle cx="12" cy="12" r="2.7" stroke="currentColor" stroke-width="1.7"/>
                            </svg>
                            <svg id="eyeClosed" class="hidden" aria-hidden="true" viewBox="0 0 24 24" fill="none">
                                <path d="m3 3 18 18M10.6 6.2A10.7 10.7 0 0 1 12 6c6.1 0 9.5 6 9.5 6a15 15 0 0 1-3 3.6M6.2 6.3C3.8 8 2.5 12 2.5 12s3.4 6 9.5 6a10.7 10.7 0 0 0 3-.4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <label class="remember-option" for="remember">
                    <input type="checkbox" name="remember" id="remember">
                    <span>Ingat saya</span>
                </label>

                <button type="submit" class="login-submit">
                    <span>Masuk</span>
                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="none">
                        <path d="M4 10h11m-4-4 4 4-4 4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </form>
        </section>

        <footer class="login-footer">Akses aman untuk operasional bisnis Anda</footer>
    </main>

    <script>
        const passwordInput = document.getElementById('passwordInput');
        const passwordToggle = document.getElementById('passwordToggle');
        const eyeOpen = document.getElementById('eyeOpen');
        const eyeClosed = document.getElementById('eyeClosed');

        passwordToggle.addEventListener('click', () => {
            const showPassword = passwordInput.type === 'password';
            passwordInput.type = showPassword ? 'text' : 'password';
            passwordToggle.setAttribute('aria-label', showPassword ? 'Sembunyikan password' : 'Tampilkan password');
            passwordToggle.setAttribute('aria-pressed', String(showPassword));
            eyeOpen.classList.toggle('hidden', showPassword);
            eyeClosed.classList.toggle('hidden', !showPassword);
        });
    </script>
</body>
</html>
