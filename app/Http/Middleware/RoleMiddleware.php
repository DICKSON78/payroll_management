<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // Hakikisha mtumiaji ame-login kabla ya kuendelea.
        if (!Auth::check()) {
            Log::info('RoleMiddleware - User not authenticated, redirecting to login');
            return redirect('/login')->with('error', 'You must be logged in to access this page.');
        }

        $user = Auth::user();
        
        // Pata jukumu la mtumiaji kwa kutumia sifa ya 'role'.
        $userRole = strtolower(trim($user->role ?? ''));
        $allowedRoles = array_map(fn($role) => strtolower(trim($role)), $roles);

        // Fanya ukaguzi wa mwisho.
        if ($userRole && in_array($userRole, $allowedRoles)) {
            Log::info('RoleMiddleware - Access granted');
            return $next($request);
        }

        // Ikiwa mtumiaji hana ruhusa.
        Log::info('RoleMiddleware - Access denied');
        return back()->with('error', 'You do not have the required permissions to access this page.');
    }
}
