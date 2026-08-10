<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't apply maintenance mode to admin, login, logout, or install routes
        if ($request->is('admin*') || $request->is('login') || $request->is('logout') || $request->is('install*')) {
            return $next($request);
        }

        try {
            // Fetch maintenance settings from site_settings
            $generalSettings = SiteSetting::getSettingsByGroup('general');
            $isMaintenanceMode = isset($generalSettings['maintenance_mode']) && in_array($generalSettings['maintenance_mode'], ['1', 'true', 'on', true], true);

            if ($isMaintenanceMode || $request->has('preview_maintenance')) {
                $title = $generalSettings['maintenance_title'] ?? 'We\'ll Be Back Soon!';
                $message = $generalSettings['maintenance_message'] ?? 'Our site is currently undergoing scheduled maintenance to serve you better. Please check back shortly.';

                $appearanceSettings = SiteSetting::getSettingsByGroup('appearance');
                $favicon = \App\Helpers\HtmlHelper::assetUrl($appearanceSettings['favicon'] ?? null);
                $logo = \App\Helpers\HtmlHelper::assetUrl($appearanceSettings['logo'] ?? null);

                return response()->view('errors.maintenance', compact('title', 'message', 'favicon', 'logo'), 503);
            }
        } catch (\Throwable $e) {
            // Silently ignore if site_settings table does not exist yet (e.g. during installation)
        }

        return $next($request);
    }
}
