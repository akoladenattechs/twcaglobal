<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request — sets essential security HTTP headers.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Only set HSTS if the request is over HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // Content-Security-Policy — relaxed for admin Summernote editors
        // and external dependencies (Bootstrap, FontAwesome, jQuery).
        // Tighten for production once all resources are audited.
        // Media domain is derived from R2_PUBLIC_URL so it always matches storage.
        $r2Url = rtrim((string) config('filesystems.disks.r2.url'), '/');
        $r2Host = $r2Url !== '' ? parse_url($r2Url, PHP_URL_HOST) : '';
        $mediaSources = "'self'";
        if ($r2Host) {
            $mediaSources .= ' https://'.$r2Host;
        }

        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://code.jquery.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://stackpath.bootstrapcdn.com https://cdn.datatables.net https://*.paystack.co https://paystack.com https://*.flutterwave.com https://flutterwave.com https://*.stripe.com https://stripe.com https://*.stripe.network https://www.googletagmanager.com https://www.google-analytics.com",
            "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://stackpath.bootstrapcdn.com https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdn.datatables.net https://paystack.com https://*.paystack.co https://*.flutterwave.com https://flutterwave.com https://*.stripe.com",
            "img-src 'self' data: blob: https:",
            "font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com https://stackpath.bootstrapcdn.com https://cdn.jsdelivr.net https://*.flutterwave.com https://*.paystack.co https://*.stripe.com",
            "connect-src 'self' https: https://*.paystack.co https://api.paystack.co https://*.flutterwave.com https://api.flutterwave.com https://*.stripe.com https://api.stripe.com https://www.google-analytics.com https://*.google-analytics.com https://*.analytics.google.com",
            'media-src '.$mediaSources,
            "frame-src 'self' https://www.youtube.com https://player.vimeo.com https://www.google.com https://checkout.paystack.com https://*.paystack.co https://paystack.com https://checkout.flutterwave.com https://checkout-v3.flutterwave.com https://*.flutterwave.com https://flutterwave.com https://js.stripe.com https://hooks.stripe.com https://m.stripe.network https://*.stripe.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        return $response;
    }
}
