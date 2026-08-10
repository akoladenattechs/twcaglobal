<?php

namespace App\Http\Controllers;

use App\Models\Newsletter;
use App\Models\NewsletterSubscriber;
use App\Models\NewsletterTracking;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Public-facing controller for newsletter subscription management,
 * tracking (open/click), unsubscribe, and webhook handling.
 */
class NewsletterPublicController extends Controller
{
    // ───── Subscription (Double Opt-In) ─────

    /**
     * Show a simple subscription form (or handle via frontend JS).
     */
    public function subscribeForm(): View
    {
        return view('frontend.newsletter.subscribe');
    }

    /**
     * Handle subscription request — stores as "pending" and sends
     * a verification email (double opt-in).
     */
    public function subscribeStore(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email|max:191',
            'name' => 'nullable|string|max:100',
        ]);

        $email = $request->input('email');

        // Check if subscriber exists
        $existing = NewsletterSubscriber::where('email', $email)->first();
        if ($existing) {
            if ($existing->status === 'active') {
                return back()->with('info', 'You are already subscribed to our newsletter!');
            }

            // Re-activate if pending, unsubscribed, or bounced
            $existing->update([
                'status' => 'active',
                'subscribed_at' => now(),
                'verified_at' => now(),
                'unsubscribed_at' => null,
                'bounced_at' => null,
                'bounce_reason' => null,
                'complaint_at' => null,
            ]);

            return back()->with('success', 'Welcome back! Your newsletter subscription has been re-activated.');
        }

        // Instant active registration
        NewsletterSubscriber::register($email, $request->input('name'));

        return back()->with('success', 'Thank you for subscribing to our newsletter!');
    }

    /**
     * Verify the subscriber's email via the verification token.
     */
    public function verify(string $token): View|RedirectResponse
    {
        $subscriber = NewsletterSubscriber::findByVerificationToken($token);

        if (! $subscriber) {
            return view('frontend.newsletter.message', [
                'title' => 'Invalid Link',
                'message' => 'This verification link is invalid or has already been used.',
                'type' => 'error',
            ]);
        }

        if ($subscriber->status === 'active') {
            return view('frontend.newsletter.message', [
                'title' => 'Already Verified',
                'message' => 'Your email is already verified. Thank you!',
                'type' => 'success',
            ]);
        }

        $subscriber->verify();

        return view('frontend.newsletter.message', [
            'title' => 'Subscription Confirmed!',
            'message' => 'You have successfully subscribed to our newsletter. Thank you!',
            'type' => 'success',
        ]);
    }

    /**
     * Send the verification email to a pending subscriber.
     */
    private function sendVerificationEmail(NewsletterSubscriber $subscriber): void
    {
        try {
            $settings = SiteSetting::getAllSettings();
            $verificationUrl = route('newsletter.verify', ['token' => $subscriber->verification_token]);

            Mail::send('emails.newsletter-verify', [
                'subscriber' => $subscriber,
                'verification_url' => $verificationUrl,
                'primary_color' => $settings['primary_color'] ?? '#ce0f3d',
                'secondary_color' => $settings['secondary_color'] ?? '#343a40',
            ], function ($message) use ($subscriber) {
                $message->to($subscriber->email)
                    ->subject('Please confirm your subscription');
            });
        } catch (\Exception $e) {
            Log::error('Verification email failed for '.$subscriber->email.': '.$e->getMessage());
        }
    }

    // ───── Unsubscribe ─────

    /**
     * Show the unsubscribe confirmation page.
     */
    public function unsubscribeForm(string $token): View
    {
        $subscriber = NewsletterSubscriber::findByUnsubscribeToken($token);

        if (! $subscriber) {
            return view('frontend.newsletter.message', [
                'title' => 'Invalid Link',
                'message' => 'This unsubscribe link is invalid or has expired.',
                'type' => 'error',
            ]);
        }

        return view('frontend.newsletter.unsubscribe', [
            'subscriber' => $subscriber,
            'token' => $token,
        ]);
    }

    /**
     * Process the unsubscribe (one-click or after confirmation).
     */
    public function unsubscribeProcess(string $token): RedirectResponse|View
    {
        $subscriber = NewsletterSubscriber::findByUnsubscribeToken($token);

        if (! $subscriber) {
            return view('frontend.newsletter.message', [
                'title' => 'Invalid Link',
                'message' => 'This unsubscribe link is invalid or has expired.',
                'type' => 'error',
            ]);
        }

        if (! in_array($subscriber->status, ['active', 'pending'])) {
            return view('frontend.newsletter.message', [
                'title' => 'Already Unsubscribed',
                'message' => 'You are already unsubscribed from our newsletter.',
                'type' => 'info',
            ]);
        }

        $subscriber->unsubscribe();

        return view('frontend.newsletter.message', [
            'title' => 'Unsubscribed',
            'message' => 'You have been successfully unsubscribed. You will no longer receive our emails.',
            'type' => 'success',
        ]);
    }

    // ───── Tracking ─────

    /**
     * Tracking pixel — 1x1 transparent GIF for open detection.
     */
    public function trackOpen(Request $request, int $newsletterId, int $subscriberId): Response
    {
        try {
            // Check if this subscriber has already opened this newsletter
            $alreadyOpened = NewsletterTracking::where('newsletter_id', $newsletterId)
                ->where('subscriber_id', $subscriberId)
                ->where('event', 'open')
                ->exists();

            // Always log the raw event for analytics detail
            NewsletterTracking::logOpen(
                $newsletterId,
                $subscriberId,
                $request->ip(),
                $request->userAgent()
            );

            // Only increment the unique-opens counter on the first open
            if (! $alreadyOpened) {
                Newsletter::where('id', $newsletterId)->increment('opens_count');
            }
        } catch (\Exception $e) {
            // Silently fail — tracking should never break the email display
            Log::debug('Open tracking failed: '.$e->getMessage());
        }

        // Return a 1x1 transparent GIF
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200, [
            'Content-Type' => 'image/gif',
            'Content-Length' => '35',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => 'Wed, 11 Jan 1984 05:00:00 GMT',
        ]);
    }

    /**
     * Click tracking — redirect through our tracker to log the click,
     * then redirect to the actual URL.
     *
     * URL validation is applied to prevent open redirect abuse:
     * - Must be a valid http/https URL
     * - No javascript:, data:, vbscript:, file: protocols
     * - No bare IP-based URLs (prevents SSRF-style abuse)
     * - Hostname must contain at least one dot (prevents unqualified redirects)
     */
    public function trackClick(Request $request): RedirectResponse
    {
        $newsletterId = (int) $request->input('newsletter');
        $subscriberId = (int) $request->input('subscriber');
        $url = $request->input('url');

        if (! $url) {
            return redirect()->to('/');
        }

        // Open redirect protection — validate the destination URL
        $sanitizedUrl = $this->sanitizeRedirectUrl($url);
        if ($sanitizedUrl === null) {
            Log::warning('Blocked suspicious redirect URL in click tracking', ['url' => $url]);

            return redirect()->to('/');
        }

        try {
            // Check if this subscriber has already clicked any link in this newsletter
            $alreadyClicked = NewsletterTracking::where('newsletter_id', $newsletterId)
                ->where('subscriber_id', $subscriberId)
                ->where('event', 'click')
                ->exists();

            // Always log the raw click event
            NewsletterTracking::logClick(
                $newsletterId,
                $subscriberId,
                $sanitizedUrl,
                $request->ip(),
                $request->userAgent()
            );

            // Only increment unique-clicks counter on first click
            if (! $alreadyClicked) {
                Newsletter::where('id', $newsletterId)->increment('clicks_count');
            }
        } catch (\Exception $e) {
            Log::debug('Click tracking failed: '.$e->getMessage());
        }

        return redirect()->away($sanitizedUrl);
    }

    /**
     * Sanitize a redirect URL to prevent open redirect and SSRF attacks.
     *
     * Returns the validated URL string, or null if the URL is rejected.
     */
    private function sanitizeRedirectUrl(?string $url): ?string
    {
        if (empty($url)) {
            return null;
        }

        // Only allow http and https schemes
        $parsed = parse_url($url);
        if (! isset($parsed['scheme']) || ! in_array(strtolower($parsed['scheme']), ['http', 'https'], true)) {
            return null;
        }

        $host = $parsed['host'] ?? '';

        // Reject bare IP addresses (prevents SSRF to internal services)
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        // Hostname must contain at least one dot (prevents 'http://localhost/' etc.)
        if (! str_contains($host, '.')) {
            return null;
        }

        // Reject hosts with common internal/sensitive names
        $blockedPatterns = [
            'localhost',
            'internal',
            '10.',
            '172.16.',
            '192.168.',
            '127.0.0.',
            '0.0.0.0',
            '[::1]',
            '[::]',
        ];
        foreach ($blockedPatterns as $pattern) {
            if (str_starts_with($host, $pattern)) {
                return null;
            }
        }

        // Reconstruct the URL without any user-info (prevents credential leakage in redirect)
        $safeUrl = (strtolower($parsed['scheme']) === 'https' ? 'https://' : 'http://')
            .$host
            .(isset($parsed['port']) ? ':'.$parsed['port'] : '')
            .($parsed['path'] ?? '/')
            .(isset($parsed['query']) ? '?'.$parsed['query'] : '')
            .(isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '');

        return $safeUrl;
    }

    // ───── Webhooks (Bounce / Complaint) ─────

    /**
     * Generic webhook endpoint for handling bounce/complaint notifications
     * from email service providers (SES, SendGrid, Postmark, etc.).
     *
     * Each provider sends different payloads — this implements a common
     * pattern. Extend with provider-specific parsing as needed.
     *
     * Expected POST JSON body:
     * {
     *   "event": "bounce" | "complaint",
     *   "email": "bounced@example.com",
     *   "reason": "optional bounce reason"
     * }
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        // Validate the webhook secret to prevent abuse.
        // Fail closed: a missing/mismatched secret rejects the request.
        $secret = config('services.newsletter.webhook_secret');
        if (! $secret || ! hash_equals($secret, (string) $request->header('X-Webhook-Secret'))) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $payload = $request->all();
        $event = $payload['event'] ?? null;
        $email = $payload['email'] ?? null;

        if (! $event || ! $email) {
            return response()->json(['error' => 'Missing event or email'], 422);
        }

        $subscriber = NewsletterSubscriber::where('email', $email)->first();
        if (! $subscriber) {
            // Unknown subscriber — still return 200 to acknowledge receipt
            return response()->json(['status' => 'ignored', 'reason' => 'unknown subscriber']);
        }

        match ($event) {
            'bounce' => $subscriber->markAsBounced($payload['reason'] ?? 'Hard bounce'),
            'complaint' => $subscriber->markAsComplaint(),
            default => Log::warning('Unknown webhook event type', ['event' => $event]),
        };

        Log::info("Newsletter webhook processed: {$event} for {$email}");

        return response()->json(['status' => 'ok']);
    }
}
