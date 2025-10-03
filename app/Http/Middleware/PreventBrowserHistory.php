<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse; // <-- Import MPYA

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

        // Angalia kama response ni mojawapo ya aina za Response zisizotumia 'withHeaders()'.
        // Hizi ni pamoja na downloads za faili (BinaryFileResponse) na responses zinazotolewa polepole (StreamedResponse).
        if ($response instanceof BinaryFileResponse || $response instanceof StreamedResponse) {
            return $response;
        }
 
        // Huongeza HTTP Headers kwenye response yoyote inayoingia hapa (isipokuwa downloads)
        // Headers hizi ni muhimu kwa kuzuia browser kuhifadhi historia ya nyuma.
        return $response->withHeaders([
            // Headers hizi huagiza kivinjari kisihifadhi ukurasa wowote
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Sun, 01 Jan 1990 00:00:00 GMT',
        ]);
    }
}
