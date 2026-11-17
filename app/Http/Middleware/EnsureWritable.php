<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mencegah role read-only (Owner) melakukan operasi tulis di SELURUH rute,
 * terlepas dari konfigurasi role per-route.
 *
 * Owner = view-only. Semua request non-GET diblokir, kecuali logout.
 */
class EnsureWritable
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if ($user->canEdit()) {
            return $next($request);
        }

        // Method aman (GET/HEAD/OPTIONS) selalu boleh.
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        // Pengecualian: keluar dari aplikasi harus tetap bisa.
        if ($request->is('logout') || $request->routeIs('logout')) {
            return $next($request);
        }

        AuditLog::record(
            'access_control',
            $request->path(),
            'write_blocked',
            $user->username ?? $user->email,
            "Role read-only [{$user->role}] diblokir saat mencoba [{$request->method()}] pada [{$request->path()}]"
        );

        if ($request->expectsJson()) {
            abort(403, 'Role Anda hanya dapat melihat data. Operasi tulis tidak diizinkan.');
        }

        return redirect()->to(url()->previous(route('home')))->with(
            'error',
            'Role Anda hanya dapat melihat data (view-only). Operasi tulis tidak diizinkan.'
        );
    }
}
