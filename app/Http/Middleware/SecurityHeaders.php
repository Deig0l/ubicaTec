<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 'unsafe-inline'/'unsafe-eval' son necesarios: los blades llevan JS
        // inline y Alpine/Livewire evalúan expresiones. La CSP sigue acotando
        // los ORÍGENES permitidos (nada de scripts de dominios ajenos).
        $script = "'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com";
        $style = "'self' 'unsafe-inline' https://unpkg.com";
        $connect = "'self' https://unpkg.com"; // unpkg: source maps de Leaflet (solo devtools)

        if (app()->environment('local')) {
            // Vite dev server (npm run dev) y su HMR por websocket.
            $vite = 'http://localhost:5173 http://127.0.0.1:5173';
            $script .= " {$vite}";
            $style .= " {$vite}";
            $connect .= " {$vite} ws://localhost:5173 ws://127.0.0.1:5173";
        }

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src {$script}",
            "style-src {$style}",
            "img-src 'self' data: https://unpkg.com https://tile.openstreetmap.org https://*.tile.openstreetmap.org",
            "connect-src {$connect}",
            "font-src 'self' data:",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(self), camera=(), microphone=(), payment=()');

        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }
}
