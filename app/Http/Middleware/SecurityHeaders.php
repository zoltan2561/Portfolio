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

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $randi = $request->is('randi', 'randi/*');
        $response->headers->set('Referrer-Policy', $randi ? 'no-referrer' : 'strict-origin-when-cross-origin');
        if ($randi) {
            $response->headers->set('Cache-Control', 'private, no-store');
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
            $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; connect-src 'self'; base-uri 'none'; form-action 'self'; frame-ancestors 'self'");
            foreach ($response->headers->getCookies() as $cookie) {
                if ($cookie->getName() === config('session.cookie')) {
                    $response->headers->setCookie($cookie->withHttpOnly(true)->withSameSite('lax')->withSecure($request->isSecure() || $cookie->isSecure()));
                }
            }
        }
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        return $response;
    }
}
