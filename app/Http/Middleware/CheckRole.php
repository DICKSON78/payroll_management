<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        Log::info('CheckRole Middleware - Request URL: ' . $request->url());
        Log::info('CheckRole Middleware - Requested roles: ' . implode(', ', $roles));

        // Hakikisha mtumiaji ame-login kabla ya kuendelea.
        if (!Auth::check()) {
            Log::info('CheckRole Middleware - User not authenticated, redirecting to login');
            return redirect()->route('login');
        }

        $user = Auth::user();
        Log::info('CheckRole Middleware - User ID: ' . $user->id . ', Email: ' . $user->email);

        // Pata jukumu la mtumiaji kwa kutumia sifa ya 'role_name'.
        $userRole = strtolower(trim($user->role_name ?? ''));
        $allowedRoles = array_map(fn($role) => strtolower(trim($role)), $roles);

        Log::info('CheckRole Middleware - User Role: ' . $userRole);
        Log::info('CheckRole Middleware - Allowed Roles: ' . implode(', ', $allowedRoles));

        // Fanya ukaguzi wa mwisho.
        if ($userRole && in_array($userRole, $allowedRoles)) {
            Log::info('CheckRole Middleware - Access granted');
            return $next($request);
        }

        // Ikiwa mtumiaji hana ruhusa.
        Log::info('CheckRole Middleware - Access denied, aborting 403');
        abort(403, 'Unauthorized action.');
    }
}
