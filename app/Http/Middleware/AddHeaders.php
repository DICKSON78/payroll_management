<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to add custom HTTP headers to the response,
 * often used for security or caching control.
 * This is aliased as 'customHeaders' in bootstrap/app.php
 */
class AddHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Mifano ya kuongeza Headers za Usalama:
        // Unaweza kuongeza headers zozote unazohitaji hapa.

        // 1. Content Security Policy (CSP) - Huuzuia mashambulizi ya XSS
        // (Inahitaji kurekebishwa kulingana na mahitaji ya mradi wako)
        // $response->header('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;");

        // 2. X-Frame-Options - Huuzuia tovuti yako isipakiwe kwenye iframe (Clickjacking protection)
        $response->header('X-Frame-Options', 'SAMEORIGIN');

        // 3. X-Content-Type-Options - Huuzuia browser kufanya MIME sniffing
        $response->header('X-Content-Type-Options', 'nosniff');

        // 4. Referrer-Policy
        $response->header('Referrer-Policy', 'no-referrer-when-downgrade');

        // 5. Strict-Transport-Security (HSTS) - Inalazimisha HTTPS (kama unatumia HTTPS)
        // $response->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');

        // 6. Permissions-Policy - Huweka ruhusa kwa APIs za browser
        $response->header('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        return $response;
    }
}
