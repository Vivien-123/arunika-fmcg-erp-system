<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Session\TokenMismatchException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof TokenMismatchException && !$request->isMethodSafe()) {
            if (!auth()->check()) {
                return redirect()->route('login')->withErrors([
                    'username' => 'Sesi keamanan telah berakhir. Silakan masuk kembali, lalu ulangi tindakan Anda.',
                ]);
            }

            $referer = $request->headers->get('referer');
            $parts = is_string($referer) ? parse_url($referer) : false;
            $refererPort = is_array($parts)
                ? (int) ($parts['port'] ?? (($parts['scheme'] ?? '') === 'https' ? 443 : 80))
                : null;
            $sameOrigin = is_array($parts)
                && isset($parts['scheme'], $parts['host'])
                && strtolower($parts['scheme']) === strtolower($request->getScheme())
                && strtolower($parts['host']) === strtolower($request->getHost())
                && $refererPort === (int) $request->getPort();

            $target = route('home');
            if ($sameOrigin) {
                $target = $request->getSchemeAndHttpHost()
                    . ($parts['path'] ?? '/')
                    . (isset($parts['query']) ? '?'.$parts['query'] : '');
            }

            return redirect()
                ->to($target)
                ->withInput($request->except(['_token', 'password', 'password_confirmation', 'current_password']))
                ->with('error', 'Sesi keamanan kedaluwarsa. Periksa kembali isian yang tampil sebelum mengulangi tindakan.');
        }

        return parent::render($request, $e);
    }
}
