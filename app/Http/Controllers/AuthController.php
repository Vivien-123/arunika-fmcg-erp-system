<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $input = $credentials['username'];
        $field = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $attempt = Auth::attempt(
            [$field => $input, 'password' => $credentials['password']],
            $request->boolean('remember')
        );

        if ($attempt) {
            $request->session()->regenerate();

            $user   = Auth::user();
            $actor  = $user->username ?? $user->email ?? 'unknown';
            $role   = $user->role ?? 'unknown';

            // Audit log — wrap in try/catch so a DB error never blocks login
            try {
                AuditLog::record(
                    'access_control',
                    $actor,
                    'login',
                    $actor,
                    "User [{$actor}] role [{$role}] logged in"
                );
            } catch (\Throwable) {
                // non-critical — continue login
            }

            return redirect()->intended(route('home'));
        }

        return back()
            ->withErrors(['username' => 'Username atau password salah.'])
            ->onlyInput('username');
    }

    public function logout(Request $request)
    {
        // Capture user info BEFORE logout
        $user  = Auth::user();
        $actor = $user ? ($user->username ?? $user->email ?? 'unknown') : 'unknown';

        // Audit BEFORE invalidating session, wrapped so it never blocks logout
        if ($user) {
            try {
                AuditLog::record(
                    'access_control',
                    $actor,
                    'logout',
                    $actor,
                    "User [{$actor}] logged out"
                );
            } catch (\Throwable) {
                // non-critical — continue logout
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
