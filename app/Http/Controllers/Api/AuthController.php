<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ManagesTwoFactor;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\TwoFactorCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    use ManagesTwoFactor;

    /**
     * Maximum failed login attempts per account before temporary lockout.
     */
    const MAX_LOGIN_ATTEMPTS = 5;

    /**
     * Lockout duration in minutes after exceeding the failed-attempt limit.
     */
    const LOGIN_LOCKOUT_MINUTES = 15;

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $username = $request->username;

        // ── Per-account lockout after repeated failed attempts ──
        $lockoutKey = 'login-lockout:'.strtolower($username);
        if (RateLimiter::tooManyAttempts($lockoutKey, self::MAX_LOGIN_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($lockoutKey);
            $minutes = max(1, (int) ceil($seconds / 60));
            $message = "Too many failed login attempts. Account temporarily locked. Try again in {$minutes} minute(s).";

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 429);
            }

            return back()->with('error', $message);
        }

        $user = User::where('username', $username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            // Record the failed attempt — generic message prevents user enumeration
            RateLimiter::hit($lockoutKey, self::LOGIN_LOCKOUT_MINUTES * 60);

            ActivityLog::record(null, 'login_failed', 'Failed admin login attempt for username: '.$username, $request);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid username or password',
                ], 401);
            }

            return back()->with('error', 'Invalid username or password');
        }

        // Deactivated accounts cannot sign in
        if ($user->status === 'inactive') {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                ], 403);
            }

            return back()->with('error', 'Access denied');
        }

        if (! $user->hasPermission('access_admin')) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied',
                ], 403);
            }

            return back()->with('error', 'Access denied');
        }

        // ── Successful login — clear any accumulated lockout ───
        RateLimiter::clear($lockoutKey);

        // ── Email 2FA verification ─────────────────────────────
        // Web admin logins (admin/login) require 2FA for EVERY user.
        // The API keeps super-admin-only 2FA (non-super-admin API clients
        // authenticate directly with their credentials).
        if (! $request->expectsJson() || $user->isSuperAdmin()) {
            $this->sendTwoFactorCode($user, $request);

            if ($request->hasSession()) {
                $request->session()->put('pending_2fa_user_id', $user->id);
                $request->session()->put('pending_2fa_email', $user->email);
                $request->session()->put('pending_2fa_remember', $request->boolean('remember'));
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Two-factor verification required. A verification code has been sent to your email.',
                    'two_factor_required' => true,
                    'email' => $user->email,
                ], 202);
            }

            return redirect()->route('admin.two-factor.verify');
        }

        $this->finalizeLogin($user, $request);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'role_id' => $user->role_id,
                ],
            ]);
        } else {
            return redirect()->route('admin.dashboard');
        }
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        ActivityLog::record($userId, 'logout', 'Admin signed out of the dashboard.', $request);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        } else {
            return redirect()->route('admin.login');
        }
    }

    public function me(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role_id' => $user->role_id,
                'role' => $user->role ? $user->role->name : null,
                'permissions' => $user->role ? $user->role->permissions->pluck('name') : [],
                'last_login' => $user->last_login,
            ],
        ]);
    }

    /**
     * Verify the 2FA code (JSON/API path).
     */
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'otp' => 'required|string|size:6',
        ]);

        $email = strtolower(trim($request->input('email')));
        $otp = trim($request->input('otp'));

        $user = User::where('email', $email)->first();
        if (! $user || $user->status === 'inactive') {
            return response()->json(['success' => false, 'message' => 'Invalid verification code'], 422);
        }

        // ── Per-email lockout check ────────────────────────────
        $lockoutKey = '2fa-lockout:'.$email;
        if (RateLimiter::tooManyAttempts($lockoutKey, 1)) {
            $seconds = RateLimiter::availableIn($lockoutKey);
            $minutes = max(1, (int) ceil($seconds / 60));

            return response()->json([
                'success' => false,
                'message' => "Too many failed attempts. Try again in {$minutes} minute(s).",
            ], 429);
        }

        // ── Hourly verification rate limit ─────────────────────
        $verifyRateKey = '2fa-verify-rate:'.$email;
        if (RateLimiter::tooManyAttempts($verifyRateKey, self::TWO_FACTOR_MAX_VERIFY_ATTEMPTS_PER_HOUR - 1)) {
            return response()->json(['success' => false, 'message' => 'Too many verification attempts. Try again later.'], 429);
        }
        RateLimiter::hit($verifyRateKey, 3600);

        // ── Total failed attempts across all records ──────────
        $totalFailedAttempts = TwoFactorCode::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHours(2))
            ->sum('attempts');

        if ($totalFailedAttempts >= self::TWO_FACTOR_MAX_TOTAL_FAILED_ATTEMPTS) {
            RateLimiter::hit($lockoutKey, self::TWO_FACTOR_LOCKOUT_DURATION_MINUTES * 60);

            return response()->json(['success' => false, 'message' => 'Too many invalid attempts. Try again later.'], 429);
        }

        // ── Find the latest unused, non-expired code ───────────
        $record = TwoFactorCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $record) {
            return response()->json(['success' => false, 'message' => 'Code expired or not found. Request a new one.'], 422);
        }

        if ($record->attempts >= self::TWO_FACTOR_MAX_ATTEMPTS_PER_RECORD) {
            $record->update(['expires_at' => now()]); // Invalidate

            return response()->json(['success' => false, 'message' => 'Too many invalid attempts. Request a new code.'], 422);
        }

        if (! $record->checkOtp($otp)) {
            $record->increment('attempts');
            $remaining = self::TWO_FACTOR_MAX_ATTEMPTS_PER_RECORD - $record->fresh()->attempts;
            ActivityLog::record($user->id, '2fa_failed', 'Invalid two-factor code entered for '.$email.'.', $request);

            return response()->json(['success' => false, 'message' => "Invalid code. {$remaining} attempt(s) remaining."], 422);
        }

        // ── Verified — complete the login ──────────────────────
        $record->update(['used_at' => now()]);
        RateLimiter::clear($lockoutKey);

        // Capture the remember-me flag before clearing the pending session keys.
        $remember = false;
        if ($request->hasSession()) {
            $remember = (bool) $request->session()->get('pending_2fa_remember', false);
            $request->session()->forget(['pending_2fa_user_id', 'pending_2fa_email', 'pending_2fa_remember']);
        }

        $this->finalizeLogin($user, $request, $remember);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'role_id' => $user->role_id,
            ],
        ]);
    }
}
