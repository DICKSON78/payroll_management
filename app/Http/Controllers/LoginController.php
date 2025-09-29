<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;
use App\Models\Employee;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // Validate credentials
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if user exists and is active
        $user = Employee::where('email', $credentials['email'])->first();

        if (!$user) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->onlyInput('email');
        }

        // Check if user is active
        if (!$user->isActive()) {
            return back()->withErrors([
                'email' => 'Your account is not active. Please contact administrator.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // Update last login
            $user->updateLastLogin($request->ip());

            // Set session lifetime based on remember me
            if ($request->filled('remember')) {
                // 30 days for remember me
                $request->session()->put('remember_me', true);
                config(['session.lifetime' => 43200]); // 30 days in minutes
            } else {
                // 30 minutes for normal session
                $request->session()->put('remember_me', false);
                config(['session.lifetime' => 30]);
            }

            // Set last activity timestamp
            Session::put('last_activity', time());

            // Redirect based on role
            if ($user->isAdmin() || $user->isHR()) {
                return redirect()->intended(route('dashboard'));
            } elseif ($user->isEmployee()) {
                return redirect()->intended(route('portal.attendance'));
            }

            // Default fallback
            return redirect()->intended('/');
        }

        // Authentication failed
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // Log user activity before logout
        if (Auth::check()) {
            $user = Auth::user();
            $user->updateLastLogout();
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been logged out successfully.');
    }

    /**
     * Show forgot password form
     */
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle forgot password request
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Check if user exists and is active
        $user = Employee::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We can\'t find a user with that email address.']);
        }

        if (!$user->isActive()) {
            return back()->withErrors(['email' => 'Your account is not active. Please contact administrator.']);
        }

        // Generate reset token
        $token = Str::random(60);

        // Store token in password_resets table
        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        // Send reset email
        $this->sendResetEmail($user, $token);

        return back()->with('status', 'We have emailed your password reset link!');
    }

    /**
     * Show reset password form
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->email]);
    }

    /**
     * Handle password reset
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        // Verify token
        $resetRecord = DB::table('password_resets')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return back()->withErrors(['email' => 'Invalid reset token.']);
        }

        // Check if token is valid (within 60 minutes)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_resets')->where('email', $request->email)->delete();
            return back()->withErrors(['email' => 'Reset token has expired.']);
        }

        // Verify token matches
        if (!Hash::check($request->token, $resetRecord->token)) {
            return back()->withErrors(['email' => 'Invalid reset token.']);
        }

        // Update user password
        $user = Employee::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'We can\'t find a user with that email address.']);
        }

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        $user->updatePasswordChangedAt();

        // Delete used token
        DB::table('password_resets')->where('email', $request->email)->delete();

        // Log the user in automatically after password reset
        Auth::login($user);

        return redirect()->route('dashboard')->with('status', 'Password reset successfully!');
    }

    /**
     * Send password reset email
     */
    private function sendResetEmail($user, $token)
    {
        // In a real application, you would send an email here
        // For now, we'll log the reset link
        $resetLink = route('password.reset', ['token' => $token, 'email' => $user->email]);

        \Log::info("Password reset link for {$user->email}: {$resetLink}");

        // Example email sending (uncomment when you have mail configured)
        /*
        Mail::send('emails.password-reset', [
            'user' => $user,
            'resetLink' => $resetLink
        ], function ($message) use ($user) {
            $message->to($user->email);
            $message->subject('Password Reset Request');
        });
        */
    }
}
