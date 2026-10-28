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
        if (!auth()->check()) return redirect()->route('login');

        $user = auth()->user();
        if (!in_array($user->role, $roles)) {
            AuditLog::record('access_control', $request->path(), 'access_denied', $user->username ?? $user->email, "Role [{$user->role}] denied access to [{$request->path()}]");
            abort(403, 'Akses ditolak: role Anda tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}
