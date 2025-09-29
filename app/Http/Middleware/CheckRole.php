<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = strtolower(auth()->user()->role ?? 'employee');
        
        // Check if user has any of the required roles
        if (!in_array($userRole, $roles)) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}