<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminHRMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Hakikisha mtumiaji ame-login kabla ya kuendelea.
        if (!Auth::check()) {
            Log::info('AdminHRMiddleware - User not authenticated, redirecting to login');
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Pata jukumu la mtumiaji kwa kutumia sifa ya 'role'.
        $userRole = strtolower(trim($user->role ?? ''));

        // Ruhusu Admin na HR tu
        if (in_array($userRole, ['admin', 'hr'])) {
            Log::info('AdminHRMiddleware - Access granted');
            return $next($request);
        }

        // Kama sio Admin wala HR -> mwambie hana ruhusa
        Log::info('AdminHRMiddleware - Access denied, aborting 403');
        abort(403, 'Unauthorized action.');
    }
}
