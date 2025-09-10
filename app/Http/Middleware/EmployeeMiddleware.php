<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EmployeeMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Hakikisha mtumiaji ame-login kabla ya kuendelea.
        if (!Auth::check()) {
            Log::info('EmployeeMiddleware - User not authenticated, redirecting to login');
            return redirect()->route('login');
        }

        $user = Auth::user();
        
        // Pata jukumu la mtumiaji kwa kutumia sifa ya 'role'.
        $userRole = strtolower(trim($user->role ?? ''));

        // Ruhusu 'Employee' tu
        if ($userRole === 'employee') {
            Log::info('EmployeeMiddleware - Access granted');
            return $next($request);
        }
        
        // Kama sio Employee, mwambie hana ruhusa
        Log::info('EmployeeMiddleware - Access denied, aborting 403');
        return redirect('/')->with('error', 'Huna ruhusa unazohitaji.');
    }
}
