<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesTwoFactor;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\TwoFactorCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class TwoFactorController extends Controller
{
    use ManagesTwoFactor;

    /**
     * Show the 2FA verification form for a pending login.
     */
    public function showVerifyForm(Request $request)
    {
        $email = $request->session()->get('pending_2fa_email');
        $userId = $request->session()->get('pending_2fa_user_id');

        if (! $email || ! $userId) {
            // Already authenticated (e.g. landed here after a duplicate submit) → go to dashboard.
            if (Auth::check()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('admin.login')
                ->with('error', 'No pending two-factor verification. Please sign in again.');
        }

        return view('admin.two-factor', ['email' => $email]);
    }

    /**
     * Verify the emailed OTP and complete the login.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
            // Honeypot: must be empty or it's a bot
            'website_url' => 'nullable|string|max:0',
        ]);

        $email = $request->session()->get('pending_2fa_email');
        $userId = $request->session()->get('pending_2fa_user_id');

        if (! $email || ! $userId) {
            // Duplicate submission right after a successful verify — the user is
            // already authenticated, so send them on instead of failing the login.
            if (Auth::check()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('admin.login')
                ->with('error', 'Session expired. Please sign in again.');
        }

        $user = User::find($userId);
        if (! $user || $user->email !== $email || $user->status === 'inactive') {
            $request->session()->forget(['pending_2fa_user_id', 'pending_2fa_email', 'pending_2fa_remember']);

            return redirect()->route('admin.login')
                ->with('error', 'Session expired. Please sign in again.');
        }

        // ── Per-email lockout check ────────────────────────────
        $lockoutKey = '2fa-lockout:'.$email;
        if (RateLimiter::tooManyAttempts($lockoutKey, 1)) {
            $seconds = RateLimiter::availableIn($lockoutKey);
            $minutes = max(1, (int) ceil($seconds / 60));

            return back()->withInput()->with('error',
                "Account temporarily locked due to too many failed attempts. Try again in {$minutes} minute(s).");
        }

        // ── Hourly verification rate limit ─────────────────────
        $verifyRateKey = '2fa-verify-rate:'.$email;
        if (RateLimiter::tooManyAttempts($verifyRateKey, self::TWO_FACTOR_MAX_VERIFY_ATTEMPTS_PER_HOUR - 1)) {
            $seconds = RateLimiter::availableIn($verifyRateKey);
            $minutes = max(1, (int) ceil($seconds / 60));

            return back()->withInput()->with('error',
                "Too many verification attempts. Please try again in {$minutes} minute(s).");
        }
        RateLimiter::hit($verifyRateKey, 3600);

        // ── Total failed attempts across all records ──────────
        $totalFailedAttempts = TwoFactorCode::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subHours(2))
            ->sum('attempts');

        if ($totalFailedAttempts >= self::TWO_FACTOR_MAX_TOTAL_FAILED_ATTEMPTS) {
            RateLimiter::hit($lockoutKey, self::TWO_FACTOR_LOCKOUT_DURATION_MINUTES * 60);

            return back()->withInput()->with('error',
                'Too many invalid attempts. Account locked for '.self::TWO_FACTOR_LOCKOUT_DURATION_MINUTES.' minutes.');
        }

        // ── Find the latest unused, non-expired code ───────────
        $record = TwoFactorCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $record) {
            return redirect()->route('admin.login')
                ->with('error', 'Code expired or not found. Please sign in again.');
        }

        if ($record->attempts >= self::TWO_FACTOR_MAX_ATTEMPTS_PER_RECORD) {
            $record->update(['expires_at' => now()]); // Invalidate

            return redirect()->route('admin.login')
                ->with('error', 'Too many invalid attempts. Please sign in again.');
        }

        if (! $record->checkOtp($request->input('otp'))) {
            $record->increment('attempts');
            $remaining = self::TWO_FACTOR_MAX_ATTEMPTS_PER_RECORD - $record->fresh()->attempts;
            ActivityLog::record($user->id, '2fa_failed', 'Invalid two-factor code entered for '.$email.'.', $request);

            return back()->withInput()->with('error', "Invalid code. {$remaining} attempt(s) remaining.");
        }

        // ── Verified — complete the login ──────────────────────
        $record->update(['used_at' => now()]);
        RateLimiter::clear($lockoutKey);
        // Capture the remember-me flag before clearing the pending session keys.
        $remember = (bool) $request->session()->get('pending_2fa_remember', false);
        $request->session()->forget(['pending_2fa_user_id', 'pending_2fa_email', 'pending_2fa_remember']);

        $this->finalizeLogin($user, $request, $remember);

        return redirect()->route('admin.dashboard');
    }

    /**
     * Resend the verification code (with cooldown).
     */
    public function resend(Request $request)
    {
        $email = $request->session()->get('pending_2fa_email');
        $userId = $request->session()->get('pending_2fa_user_id');

        if (! $email || ! $userId) {
            // Duplicate submit after a successful verify — already authenticated.
            if (Auth::check()) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('admin.login')
                ->with('error', 'Session expired. Please sign in again.');
        }

        $user = User::find($userId);
        if (! $user || $user->email !== $email || $user->status === 'inactive') {
            $request->session()->forget(['pending_2fa_user_id', 'pending_2fa_email', 'pending_2fa_remember']);

            return redirect()->route('admin.login')
                ->with('error', 'Session expired. Please sign in again.');
        }

        // ── Per-email resend cooldown ──────────────────────────
        $rateKey = '2fa-send:'.$email;
        if (RateLimiter::tooManyAttempts($rateKey, 1)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return back()->withInput()->with('error',
                "Please wait {$seconds} seconds before requesting another code.");
        }

        $this->sendTwoFactorCode($user, $request);
        RateLimiter::hit($rateKey, self::TWO_FACTOR_RESEND_COOLDOWN_SECONDS);

        return back()->with('success', 'A new verification code has been sent to your email.');
    }
}
