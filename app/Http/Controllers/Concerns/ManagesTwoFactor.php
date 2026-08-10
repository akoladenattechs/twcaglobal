<?php

namespace App\Http\Controllers\Concerns;

use App\Mail\LoginNotificationMail;
use App\Mail\TwoFactorCodeMail;
use App\Models\ActivityLog;
use App\Models\SiteSetting;
use App\Models\TwoFactorCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

/**
 * Shared logic for the email OTP two-factor login flow.
 */
trait ManagesTwoFactor
{
    /** Maximum OTP attempts per code record before invalidation. */
    const TWO_FACTOR_MAX_ATTEMPTS_PER_RECORD = 3;

    /** Maximum total failed attempts across all records before lockout. */
    const TWO_FACTOR_MAX_TOTAL_FAILED_ATTEMPTS = 5;

    /** OTP expiry in minutes. */
    const TWO_FACTOR_EXPIRY_MINUTES = 10;

    /** Cooldown between code resend requests (seconds). */
    const TWO_FACTOR_RESEND_COOLDOWN_SECONDS = 60;

    /** Maximum verification attempts per email per hour (rate limit). */
    const TWO_FACTOR_MAX_VERIFY_ATTEMPTS_PER_HOUR = 10;

    /** Lockout duration in minutes after exceeding total failed attempts. */
    const TWO_FACTOR_LOCKOUT_DURATION_MINUTES = 30;

    /**
     * Generate a cryptographically secure 6-digit code, store it hashed,
     * and email it to the user. Any existing unused codes are invalidated.
     */
    protected function sendTwoFactorCode(User $user, ?Request $request = null): void
    {
        // Invalidate any existing unused codes for this user
        TwoFactorCode::where('user_id', $user->id)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->update(['expires_at' => now()]);

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        TwoFactorCode::create([
            'user_id' => $user->id,
            'token' => Hash::make($otp),
            'expires_at' => now()->addMinutes(self::TWO_FACTOR_EXPIRY_MINUTES),
            'attempts' => 0,
        ]);

        try {
            $settings = SiteSetting::getAllSettings();
            $siteTitle = $settings['site_title'] ?? config('app.name');
            Mail::to($user->email)->send(new TwoFactorCodeMail($otp, $siteTitle));
        } catch (\Exception $e) {
            logger()->error('Failed to send 2FA code to '.$user->email.': '.$e->getMessage());
        }

        ActivityLog::record($user->id, '2fa_sent', 'Two-factor verification code sent to '.$user->email.'.', $request);
    }

    /**
     * Complete the login: authenticate, regenerate the session to prevent
     * fixation, record last_login, and send the login-notification email.
     */
    protected function finalizeLogin(User $user, Request $request, ?bool $remember = null): void
    {
        // Preserve the "remember me" choice made on the login form. The 2FA
        // verify paths pass the value explicitly because they clear the
        // pending session keys before finalizing.
        $remember = $remember ?? $request->session()->get('pending_2fa_remember', $request->boolean('remember'));

        Auth::login($user, $remember);
        $request->session()->regenerate();

        // Record the successful sign-in in the audit log.
        ActivityLog::record($user->id, 'login', 'Admin signed in to the dashboard.', $request);

        // last_login is non-critical — never let a failure here break a
        // successful verification / login.
        try {
            $user->last_login = now();
            $user->save();
        } catch (\Exception $e) {
            logger()->error('Failed to update last_login for user '.$user->id.': '.$e->getMessage());
        }

        try {
            $settings = SiteSetting::getAllSettings();
            $siteTitle = $settings['site_title'] ?? config('app.name');
            $userName = trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: $user->username;
            Mail::to($user->email)->send(new LoginNotificationMail(
                $userName,
                $siteTitle,
                $request->ip() ?? $request->header('X-Forwarded-For') ?? 'Unknown',
                now()->format('F j, Y \a\t g:i A T')
            ));
        } catch (\Exception $e) {
            // Log the failure but don't block login
            logger()->error('Failed to send login notification email: '.$e->getMessage());
        }
    }
}
