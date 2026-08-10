<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PasswordChangedMail;
use App\Mail\SendOtpMail;
use App\Models\ActivityLog;
use App\Models\PasswordReset;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ForgotPasswordController extends Controller
{
    /**
     * Maximum allowed OTP attempts per record before invalidation.
     */
    const MAX_OTP_ATTEMPTS_PER_RECORD = 3;

    /**
     * Maximum total failed OTP attempts across all records before lockout.
     */
    const MAX_TOTAL_FAILED_ATTEMPTS = 5;

    /**
     * OTP expiry in minutes.
     */
    const OTP_EXPIRY_MINUTES = 10;

    /**
     * Cooldown between OTP resend requests (seconds).
     */
    const RESEND_COOLDOWN_SECONDS = 60;

    /**
     * Maximum OTP verification attempts per email per hour (rate limit).
     */
    const MAX_VERIFY_ATTEMPTS_PER_HOUR = 10;

    /**
     * Lockout duration in minutes after exceeding total failed attempts.
     */
    const LOCKOUT_DURATION_MINUTES = 30;

    // ─────────────────────────────────────────────────────────────
    //  STEP 1 — Show Forgot Password form (enter email)
    // ─────────────────────────────────────────────────────────────

    public function showForgotForm()
    {
        return view('admin.forgot-password');
    }

    // ─────────────────────────────────────────────────────────────
    //  STEP 1b — Send OTP to email
    // ─────────────────────────────────────────────────────────────

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            // Honeypot: must be empty or it's a bot
            'website_url' => 'nullable|string|max:0',
        ]);

        $email = strtolower(trim($request->input('email')));

        // ── Per-email global lockout check ──────────────────────
        $lockoutKey = 'otp-lockout:'.$email;
        if (RateLimiter::tooManyAttempts($lockoutKey, 1)) {
            $seconds = RateLimiter::availableIn($lockoutKey);
            $minutes = ceil($seconds / 60);

            return back()->withInput()->with('error',
                "Account temporarily locked due to too many failed attempts. Try again in {$minutes} minute(s).");
        }

        // ── Per-email send rate limit ───────────────────────────
        $rateKey = 'otp-send:'.$email;
        if (RateLimiter::tooManyAttempts($rateKey, 1)) {
            $seconds = RateLimiter::availableIn($rateKey);

            return back()->withInput()->with('error', "Please wait {$seconds} seconds before requesting another OTP.");
        }

        // ── Check if user exists ────────────────────────────────
        $user = User::where('email', $email)->first();
        if (! $user) {
            // Generic message to prevent email enumeration
            return redirect()->route('admin.forgot-password.verify-form', ['email' => $email])
                ->with('success', 'If that email exists in our system, an OTP has been sent.');
        }

        // ── Check if user account is active ─────────────────────
        if ($user->status === 'inactive') {
            $this->logActivity(null, 'password_reset_blocked_inactive',
                "Password reset blocked for inactive account: {$email}",
                $request);

            return redirect()->route('admin.forgot-password.verify-form', ['email' => $email])
                ->with('success', 'If that email exists in our system, an OTP has been sent.');
        }

        // ── Invalidate any existing unused OTPs for this email ──
        PasswordReset::where('email', $email)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);

        // ── Generate cryptographically secure 6-digit OTP ───────
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // ── Store hashed OTP (never store plaintext) ────────────
        PasswordReset::create([
            'email' => $email,
            'token' => Hash::make($otp),
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
            'attempts' => 0,
        ]);

        // ── Get site title ──────────────────────────────────────
        $siteTitle = $this->getSiteTitle();

        // ── Send OTP via email ──────────────────────────────────
        try {
            Mail::to($email)->send(new SendOtpMail($otp, $siteTitle));
        } catch (\Exception $e) {
            $this->logActivity($user->id, 'password_reset_email_failed',
                "Failed to send OTP email to: {$email} - {$e->getMessage()}",
                $request);

            return back()->withInput()->with('error', 'Failed to send OTP email. Please check mail configuration and try again.');
        }

        // ── Record rate limit hit ───────────────────────────────
        RateLimiter::hit($rateKey, self::RESEND_COOLDOWN_SECONDS);

        // ── Log the activity ────────────────────────────────────
        $this->logActivity($user->id, 'password_reset_otp_sent',
            "OTP sent to: {$email}",
            $request);

        return redirect()->route('admin.forgot-password.verify-form', ['email' => $email])
            ->with('success', 'A 6-digit OTP has been sent to your email. It expires in 10 minutes.');
    }

    // ─────────────────────────────────────────────────────────────
    //  STEP 2 — Show Verify OTP form
    // ─────────────────────────────────────────────────────────────

    public function showVerifyOtpForm(Request $request)
    {
        $email = $request->query('email');

        if (! $email) {
            return redirect()->route('admin.forgot-password')
                ->with('error', 'Session expired. Please start again.');
        }

        return view('admin.verify-otp', ['email' => $email]);
    }

    // ─────────────────────────────────────────────────────────────
    //  STEP 2b — Verify OTP
    // ─────────────────────────────────────────────────────────────

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'otp' => 'required|string|size:6',
            'website_url' => 'nullable|string|max:0', // Honeypot
        ]);

        $email = strtolower(trim($request->input('email')));
        $otp = trim($request->input('otp'));

        // ── Per-email global lockout check ──────────────────────
        $lockoutKey = 'otp-lockout:'.$email;
        if (RateLimiter::tooManyAttempts($lockoutKey, 1)) {
            $seconds = RateLimiter::availableIn($lockoutKey);
            $minutes = ceil($seconds / 60);

            return back()->withInput()->with('error',
                "Account temporarily locked due to too many failed attempts. Try again in {$minutes} minute(s).");
        }

        // ── Global rate limit on OTP verification per email ─────
        $verifyRateKey = 'otp-verify-rate:'.$email;
        if (RateLimiter::tooManyAttempts($verifyRateKey, self::MAX_VERIFY_ATTEMPTS_PER_HOUR - 1)) {
            $seconds = RateLimiter::availableIn($verifyRateKey);
            $minutes = ceil($seconds / 60);

            return back()->withInput()->with('error',
                "Too many verification attempts. Please try again in {$minutes} minute(s).");
        }
        RateLimiter::hit($verifyRateKey, 3600);

        // ── Find total failed attempts across all records ───────
        $totalFailedAttempts = PasswordReset::where('email', $email)
            ->where('created_at', '>=', now()->subHours(2))
            ->sum('attempts');

        if ($totalFailedAttempts >= self::MAX_TOTAL_FAILED_ATTEMPTS) {
            // Lock the email for LOCKOUT_DURATION_MINUTES
            RateLimiter::hit($lockoutKey, self::LOCKOUT_DURATION_MINUTES * 60);
            $this->logActivity(null, 'password_reset_lockout',
                "Account locked for {$email} due to {$totalFailedAttempts} total failed OTP attempts.",
                $request);

            return back()->withInput()->with('error',
                'Too many invalid attempts. Account locked for '.self::LOCKOUT_DURATION_MINUTES.' minutes.');
        }

        // ── Find the latest unused, non-expired OTP record ──────
        $resetRecord = PasswordReset::where('email', $email)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $resetRecord) {
            $this->logActivity(null, 'password_reset_otp_missing',
                "OTP verification attempted for {$email} but no valid OTP found.",
                $request);

            return redirect()->route('admin.forgot-password')
                ->with('error', 'OTP expired or not found. Please request a new one.');
        }

        // ── Check if max attempts per record exceeded ───────────
        if ($resetRecord->attempts >= self::MAX_OTP_ATTEMPTS_PER_RECORD) {
            $resetRecord->update(['expires_at' => now()]); // Invalidate
            $this->logActivity(null, 'password_reset_otp_exhausted',
                "OTP record exhausted for {$email} after {$resetRecord->attempts} attempts.",
                $request);

            return redirect()->route('admin.forgot-password')
                ->with('error', 'Too many invalid attempts. Please request a new OTP.');
        }

        // ── Verify OTP against stored hash ──────────────────────
        if (! $resetRecord->checkOtp($otp)) {
            $resetRecord->increment('attempts');
            $remaining = self::MAX_OTP_ATTEMPTS_PER_RECORD - ($resetRecord->fresh()->attempts);

            $this->logActivity(null, 'password_reset_otp_invalid',
                "Invalid OTP entered for {$email}. {$remaining} attempt(s) remaining on this OTP.",
                $request);

            return back()->withInput()->with('error', "Invalid OTP. {$remaining} attempt(s) remaining.");
        }

        // ── OTP is valid — generate verification token ──────────
        // This prevents direct URL access to the reset form without valid OTP
        $verificationToken = bin2hex(random_bytes(32));

        $request->session()->put('reset_verified_email', $email);
        $request->session()->put('reset_verified_token', $verificationToken);
        $request->session()->put('reset_record_id', $resetRecord->id);

        $this->logActivity(null, 'password_reset_otp_verified',
            "OTP verified successfully for {$email}.",
            $request);

        return redirect()->route('admin.forgot-password.reset-form', [
            'email' => $email,
            'token' => $verificationToken,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  STEP 3 — Show Reset Password form
    // ─────────────────────────────────────────────────────────────

    public function showResetForm(Request $request)
    {
        $email = $request->query('email');
        $token = $request->query('token');

        // Validate that this is a legitimate flow by checking session
        if (
            ! $email || ! $token ||
            $request->session()->get('reset_verified_email') !== $email ||
            $request->session()->get('reset_verified_token') !== $token
        ) {
            return redirect()->route('admin.forgot-password')
                ->with('error', 'Invalid or expired reset session. Please start again.');
        }

        return view('admin.reset-password', [
            'email' => $email,
            'token' => $token,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    //  STEP 3b — Reset Password
    // ─────────────────────────────────────────────────────────────

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'website_url' => 'nullable|string|max:0', // Honeypot
        ]);

        $email = strtolower(trim($request->input('email')));
        $token = trim($request->input('token'));
        $password = $request->input('password');

        // ── Validate session-bound verification ─────────────────
        if (
            $request->session()->get('reset_verified_email') !== $email ||
            $request->session()->get('reset_verified_token') !== $token
        ) {
            $this->logActivity(null, 'password_reset_session_mismatch',
                "Reset session mismatch for {$email} — possible CSRF or session hijacking attempt.",
                $request);

            return redirect()->route('admin.forgot-password')
                ->with('error', 'Invalid or expired reset session. Please start again.');
        }

        // ── Find the OTP record and ensure it's still valid ─────
        $recordId = $request->session()->get('reset_record_id');
        $resetRecord = PasswordReset::where('id', $recordId)
            ->where('email', $email)
            ->whereNull('used_at')
            ->first();

        if (! $resetRecord || ! $resetRecord->isValid()) {
            $request->session()->forget(['reset_verified_email', 'reset_verified_token', 'reset_record_id']);
            $this->logActivity(null, 'password_reset_otp_expired',
                "Reset session expired for {$email} — OTP record no longer valid.",
                $request);

            return redirect()->route('admin.forgot-password')
                ->with('error', 'Reset session expired. Please start again.');
        }

        // ── Find the user ───────────────────────────────────────
        $user = User::where('email', $email)->first();
        if (! $user) {
            $request->session()->forget(['reset_verified_email', 'reset_verified_token', 'reset_record_id']);
            $this->logActivity(null, 'password_reset_user_missing',
                "Password reset attempted for {$email} but user no longer exists.",
                $request);

            return redirect()->route('admin.forgot-password')
                ->with('error', 'Account not found. Please contact support.');
        }

        // ── Enforce password history (don't allow reusing same password) ──
        if (Hash::check($password, $user->password)) {
            return back()->withInput()->with('error',
                'New password must be different from your current password.');
        }

        // ── Update the user's password ──────────────────────────
        $user->password = Hash::make($password);

        // ── Invalidate & rotate "remember me" tokens ─────────────
        $user->setRememberToken(\Illuminate\Support\Str::random(60));

        $user->save();

        // ── Mark OTP as used (one-time use enforced) ────────────
        $resetRecord->update(['used_at' => now()]);

        // ── Invalidate ALL other unused OTPs for this email ─────
        PasswordReset::where('email', $email)
            ->whereNull('used_at')
            ->where('id', '!=', $resetRecord->id)
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);

        // ── Send password changed notification email ────────────
        try {
            $siteTitle = $this->getSiteTitle();
            $userName = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: $user->username;
            Mail::to($email)->send(new PasswordChangedMail(
                $userName,
                $siteTitle,
                $this->getClientIp($request),
                now()->format('F j, Y \a\t g:i A T')
            ));
        } catch (\Exception $e) {
            // Log the failure but don't block the password reset
            $this->logActivity($user->id, 'password_reset_notify_failed',
                "Failed to send password changed notification to {$email}: {$e->getMessage()}",
                $request);
        }

        // ── Log the activity ────────────────────────────────────
        $this->logActivity($user->id, 'password_reset_completed',
            "Password reset completed successfully for {$email}.",
            $request);

        // ── Clear session data ──────────────────────────────────
        $request->session()->forget(['reset_verified_email', 'reset_verified_token', 'reset_record_id']);

        return redirect()->route('admin.login')
            ->with('success', 'Password reset successful. You can now log in with your new password.');
    }

    // ═════════════════════════════════════════════════════════════
    //  PRIVATE HELPERS
    // ═════════════════════════════════════════════════════════════

    /**
     * Log an activity to the activity_logs table.
     */
    private function logActivity(?int $userId, string $action, string $description, Request $request): void
    {
        try {
            ActivityLog::create([
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'ip_address' => $this->getClientIp($request),
                'user_agent' => $request->userAgent() ?? 'Unknown',
            ]);
        } catch (\Exception $e) {
            // Fail silently — logging should never block the flow
        }
    }

    /**
     * Get the client IP address from the request.
     */
    private function getClientIp(Request $request): string
    {
        return $request->ip() ?? $request->header('X-Forwarded-For') ?? 'Unknown';
    }

    /**
     * Get the site title from settings.
     */
    private function getSiteTitle(): string
    {
        try {
            $settings = SiteSetting::getAllSettings();

            return $settings['site_title'] ?? config('app.name');
        } catch (\Exception $e) {
            return config('app.name');
        }
    }
}
