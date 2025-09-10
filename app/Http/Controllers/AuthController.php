<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * Show the initial setup form for the admin.
     * This is only accessible if no admin user exists.
     */
    public function showAdminSetup()
    {
        // Check if an admin user already exists
        if (User::where('role', 'admin')->exists()) {
            return redirect()->route('login');
        }
        return view('auth.admin_setup');
    }

    /**
     * Create the default admin user account.
     */
    public function setupAdmin(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'admin',
            ]);

            // Create corresponding employee record for admin
            Employee::create([
                'name' => $request->name,
                'employee_id' => 'EMP-ADMIN-001',
                'email' => $request->email,
                'department' => 'Administration',
                'position' => 'System Administrator',
                'base_salary' => 0,
                'hire_date' => now(),
                'user_id' => $user->id,
            ]);
        });

        Auth::login(User::where('email', $request->email)->first());

        return redirect()->route('dashboard');
    }

    /**
     * Create default admin if none exists (for seeding)
     */
    public function createDefaultAdmin()
    {
        if (!User::where('role', 'admin')->exists()) {
            DB::transaction(function () {
                $user = User::create([
                    'name' => 'System Administrator',
                    'email' => 'admin@payroll.com',
                    'password' => Hash::make('admin123'),
                    'role' => 'admin',
                ]);

                Employee::create([
                    'name' => 'System Administrator',
                    'employee_id' => 'EMP-ADMIN-001',
                    'email' => 'admin@payroll.com',
                    'department' => 'Administration',
                    'position' => 'System Administrator',
                    'base_salary' => 0,
                    'hire_date' => now(),
                    'user_id' => $user->id,
                ]);
            });
        }
    }
}
