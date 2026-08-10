<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request — sets CORS headers for the API.
     *
     * Allowed origins come from the CORS_ALLOWED_ORIGINS env var (comma-separated),
     * defaulting to the app's own URL. Wildcards are never combined with credentials.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $origin = $request->headers->get('Origin');
        $allowedOrigins = $this->allowedOrigins();

        // Only emit CORS headers for browsers that send an Origin header
        // and when that origin is explicitly allowed. Same-origin/non-browser
        // requests simply pass through without CORS headers.
        if ($origin && in_array($origin, $allowedOrigins, true)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Vary', 'Origin');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin');
            $response->headers->set('Access-Control-Max-Age', '86400');
        }

        // Handle preflight OPTIONS requests
        if ($request->isMethod('OPTIONS')) {
            return response('', 204)->withHeaders($response->headers->all());
        }

        return $response;
    }

    /**
     * Resolve the list of allowed origins.
     */
    private function allowedOrigins(): array
    {
        // config() (not env()) so this survives php artisan config:cache on production.
        $configured = config('services.cors.allowed_origins');

        if (! empty($configured)) {
            return array_values(array_filter(array_map('trim', explode(',', $configured))));
        }

        // Default: the app's own URL (API is consumed same-origin by default).
        $appUrl = config('app.url');

        return $appUrl ? [$appUrl] : [];
    }
}
