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
        if (!Auth::check()) {
            Log::info('RoleMiddleware - User not authenticated, redirecting to login', ['route' => $request->route()->getName()]);
            return redirect()->route('login')->with('error', 'You must be logged in to access this page.');
        }

        $user = Auth::user();

        // REKEBISHO MUHIMU HAPA: Tumia uhusiano wa 'role' kupata jina la jukumu.
        // Ikiwa mtumiaji hana jukumu, tumia 'employee' kama default, kama ilivyo kwenye Controller.
        $userRole = strtolower($user->role->name ?? 'employee');

        // Boresha hili: Tumia array_map kuhakikisha majukumu yote yanakuwa herufi ndogo na bila nafasi tupu.
        $allowedRoles = array_map(fn($role) => strtolower(trim($role)), $roles);

        Log::info('RoleMiddleware - Checking permissions', [
            'user_id' => $user->id,
            'user_role' => $userRole,
            'allowed_roles' => $allowedRoles,
            'route_name' => $request->route()->getName(),
        ]);

        // Kwanza angalia kama role ipo kwenye allowedRoles
        if (in_array($userRole, $allowedRoles)) {
            Log::info("RoleMiddleware - Access granted for role: {$userRole}");

            // Direct user kulingana na role yake
            if ($userRole === 'employee' && $request->route()->getName() === 'dashboard') {
                Log::info('RoleMiddleware - Redirecting employee from dashboard to portal attendance');
                return redirect()->route('portal.attendance');
            }
            
            // Hili sharti la uelekezaji linaonekana batili:
            // Kama Admin au HR Manager anataka kufikia employee.portal, mpe ruhusa badala ya kumuelekeza.
            // Lakini nitaacha iwe sawa na msimbo wako ili kuepuka mabadiliko makubwa:
            if (in_array($userRole, ['admin', 'hr manager']) && $request->route()->getName() === 'employee.portal') {
                 Log::info('RoleMiddleware - Redirecting admin/hr from employee portal to dashboard');
                 return redirect()->route('dashboard');
             }

            return $next($request);
        }

        // Ikiwa hana ruhusa
        Log::warning("RoleMiddleware - Access denied for role: {$userRole}", [
            'user_id' => $user->id,
            'allowed_roles' => $allowedRoles,
            'route_name' => $request->route()->getName(),
        ]);
        abort(403, 'You do not have the required permissions to access this page.');
    }
}
