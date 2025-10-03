<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

        try {
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

            // Send reset email using Gmail
            $this->sendResetEmail($user, $token);

            return back()->with('status', 'We have emailed your password reset link!');

        } catch (\Exception $e) {
            \Log::error('Password reset error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Failed to send reset email. Please try again later.']);
        }
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

        try {
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

        } catch (\Exception $e) {
            \Log::error('Password reset error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Failed to reset password. Please try again.']);
        }
    }

    /**
     * Send password reset email using Gmail SMTP
     */
    private function sendResetEmail($user, $token)
    {
        try {
            $resetLink = route('password.reset', ['token' => $token, 'email' => $user->email]);

            // Send email using Laravel Mail with Gmail SMTP
            Mail::send('emails.password-reset', [
                'user' => $user,
                'resetLink' => $resetLink,
                'expiryTime' => now()->addMinutes(60)->format('F j, Y, g:i A'),
                'token' => $token // For debugging purposes
            ], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('🔐 Password Reset Request - ' . config('app.name'));
                $message->from(config('mail.from.address'), config('mail.from.name'));
            });

            // Log successful email sending
            \Log::info("✅ Password reset email sent via Gmail to: {$user->email}");
            \Log::info("📧 Reset link: {$resetLink}");

            return true;

        } catch (\Exception $e) {
            // Log the error details
            \Log::error("❌ Failed to send password reset email to {$user->email}: " . $e->getMessage());
            \Log::error("📧 Error details: " . $e->getFile() . ':' . $e->getLine());
            
            throw new \Exception('Failed to send email. Please check your email configuration.');
        }
    }

    /**
     * Test email configuration
     */
    public function testEmail(Request $request)
    {
        try {
            $user = Employee::first();
            
            if (!$user) {
                return response()->json(['error' => 'No user found in database'], 404);
            }

            $token = Str::random(60);
            $resetLink = route('password.reset', ['token' => $token, 'email' => $user->email]);

            Mail::send('emails.password-reset', [
                'user' => $user,
                'resetLink' => $resetLink,
                'expiryTime' => now()->addMinutes(60)->format('F j, Y, g:i A')
            ], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('🧪 Test Email - ' . config('app.name'));
                $message->from(config('mail.from.address'), config('mail.from.name'));
            });

            \Log::info("✅ Test email sent successfully to: {$user->email}");

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully!',
                'recipient' => $user->email,
                'mail_config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name')
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("❌ Test email failed: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'mail_config' => [
                    'driver' => config('mail.default'),
                    'host' => config('mail.mailers.smtp.host'),
                    'port' => config('mail.mailers.smtp.port'),
                    'from_address' => config('mail.from.address'),
                    'from_name' => config('mail.from.name')
                ]
            ], 500);
        }
    }
}