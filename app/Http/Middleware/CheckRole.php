<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // MasterAdmin can access anything
        if ($user->role === 'MasterAdmin') {
            return $next($request);
        }

        // Check if user has required role
        if (!in_array($user->role, $roles)) {
            AuditLog::record(
                'access_control',
                $request->path(),
                'access_denied',
                $user->username ?? $user->email,
                "Role [{$user->role}] denied access to [{$request->path()}] (required: ".implode('|',$roles).")"
            );
            abort(403, 'Akses ditolak: role Anda tidak memiliki izin untuk halaman ini.');
        }

        // Owner is read-only: block any non-GET/HEAD request
        if ($user->role === 'Owner' && !$request->isMethod('GET') && !$request->isMethod('HEAD')) {
            AuditLog::record(
                'access_control',
                $request->path(),
                'write_blocked',
                $user->username ?? $user->email,
                "Owner [{$user->username}] attempted write operation [{$request->method()}] on [{$request->path()}] — blocked (read-only role)"
            );
            abort(403, 'Owner hanya dapat melihat data. Operasi tulis tidak diizinkan.');
        }

        return $next($request);
    }
}
