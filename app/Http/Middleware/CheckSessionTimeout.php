<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class CheckSessionTimeout
{
    public function handle(Request $request, Closure $next)
    {
        // Skip session timeout check for login and public routes
        if ($this->shouldSkipCheck($request)) {
            return $next($request);
        }

        if (Auth::check()) {
            $lastActivity = Session::get('last_activity');
            $rememberMe = Session::get('remember_me', false);
            
            // Set session lifetime based on remember me
            if ($rememberMe) {
                $sessionLifetime = 43200; // 30 days in minutes
                $autoLogoutTime = 60; // 60 minutes for remember me
            } else {
                $sessionLifetime = 30; // 30 minutes
                $autoLogoutTime = 10; // 10 minutes for auto logout
            }

            // Check if session has expired (only if last activity exists)
            if ($lastActivity && (time() - $lastActivity > $sessionLifetime * 60)) {
                $this->forceLogout($request);
                return redirect()->route('login')->withErrors([
                    'session' => 'Your session has expired due to inactivity.'
                ]);
            }
            
            // Check for auto logout after inactivity (only if last activity exists)
            if ($lastActivity && (time() - $lastActivity > $autoLogoutTime * 60)) {
                $this->forceLogout($request, true);
                return redirect()->route('login')->withErrors([
                    'session' => 'You have been automatically logged out due to inactivity.'
                ]);
            }
            
            // Update last activity timestamp
            Session::put('last_activity', time());
        }

        return $next($request);
    }

    /**
     * Check if we should skip session timeout for this request
     */
    private function shouldSkipCheck(Request $request): bool
    {
        $skipRoutes = [
            'login',
            'password.request',
            'password.email', 
            'password.reset',
            'password.update',
            'logout'
        ];

        $currentRoute = $request->route()->getName();

        return in_array($currentRoute, $skipRoutes) || !Auth::check();
    }

    private function forceLogout(Request $request, $autoLogout = false)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->update([
                'last_logout_at' => Carbon::now(),
                'auto_logout' => $autoLogout
            ]);
        }

        Auth::logout();
        Session::invalidate();
        Session::regenerateToken();
    }
}