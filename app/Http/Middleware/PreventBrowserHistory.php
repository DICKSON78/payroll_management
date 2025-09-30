<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventBrowserHistory
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Piga request kwanza
        $response = $next($request);

        // Huongeza HTTP Headers kwenye response yoyote inayoingia hapa
        return $response->withHeaders([
            // Headers hizi huagiza kivinjari kisihifadhi ukurasa wowote
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sun, 01 Jan 1990 00:00:00 GMT',
        ]);
    }
}