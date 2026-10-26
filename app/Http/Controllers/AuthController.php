<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('home');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $field   = filter_var($credentials['username'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $attempt = Auth::attempt([$field => $credentials['username'], 'password' => $credentials['password']], $request->boolean('remember'));

        if ($attempt) {
            $request->session()->regenerate();
            $user = Auth::user();
            AuditLog::record('access_control', $user->username ?? $user->email, 'login', $user->username ?? $user->email, "User [{$user->username}] role [{$user->role}] logged in");
            return redirect()->intended(route('home'));
        }

        return back()->withErrors(['username' => 'Username atau password salah.'])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::record('access_control', $user->username ?? $user->email, 'logout', $user->username ?? $user->email, "User [{$user->username}] logged out");
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
