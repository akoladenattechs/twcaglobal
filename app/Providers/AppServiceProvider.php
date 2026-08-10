<?php

namespace App\Providers;

use App\Models\Menu;
use App\Models\SiteSetting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Global API rate limit (120 req/min per user/IP) — covers all public /api endpoints.
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));

        // Fix for MySQL key length with utf8 (kept for compatibility with older MySQL versions)
        Schema::defaultStringLength(191);

        // Force the root URL so url(), route(), and asset() use the correct base
        URL::forceRootUrl(config('app.url'));

        // Dynamically apply non-sensitive mail settings from the database if available.
        // .env values take precedence — DB settings only apply when .env has no value.
        // Sensitive credentials (SMTP password, API keys) are managed exclusively via .env.
        try {
            if (Schema::hasTable('site_settings')) {
                $mailSettings = SiteSetting::getSettingsByGroup('mail');

                if (! empty($mailSettings)) {
                    // ── Transport ──
                    // Use config() lookups (not env()) so this survives config:cache.
                    $transport = config('mail.default');
                    if (empty($transport) || ! in_array($transport, ['smtp', 'resend', 'mailgun', 'ses', 'postmark', 'log', 'array'], true)) {
                        $transport = $mailSettings['mail_transport'];
                    }

                    // ── Sender details (only if .env has no value) ──
                    if (empty(config('mail.from.address')) && ! empty($mailSettings['from_email'])) {
                        config([
                            'mail.from.address' => $mailSettings['from_email'],
                            'mail.from.name' => $mailSettings['from_name'] ?? config('app.name'),
                        ]);
                    }

                    // ── Transport credentials (from .env only for security) ──
                    if ($transport === 'resend') {
                        config([
                            'mail.default' => 'resend',
                            'services.resend.key' => config('services.resend.key'),
                        ]);
                    } else {
                        if (empty(config('mail.default')) || config('mail.default') === 'log') {
                            config(['mail.default' => 'smtp']);
                        }

                        // Non-sensitive SMTP settings (host/port/username/encryption)
                        if (! empty($mailSettings['smtp_host']) && empty(config('mail.mailers.smtp.host'))) {
                            $encryption = $mailSettings['smtp_encryption'] ?? 'tls';
                            if ($encryption === 'none' || empty($encryption)) {
                                $encryption = null;
                            }

                            config([
                                'mail.mailers.smtp.host' => $mailSettings['smtp_host'],
                                'mail.mailers.smtp.port' => $mailSettings['smtp_port'],
                                'mail.mailers.smtp.username' => $mailSettings['smtp_username'],
                                'mail.mailers.smtp.password' => config('mail.mailers.smtp.password'),
                                'mail.mailers.smtp.encryption' => $encryption,
                            ]);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // Silently fail — mail settings from DB are a best-effort enhancement
        }

        // Share site settings with all views
        View::composer('*', function ($view) {
            try {
                $siteSettings = SiteSetting::getAllSettings();
            } catch (\Throwable $e) {
                $siteSettings = [];
            }

            try {
                // Get main menu with children
                $mainMenu = Menu::where('location', 'main_menu')
                    ->where('status', 'active')
                    ->first();
                $menuItems = $mainMenu
                    ? $mainMenu->menuItems()->where('status', 'active')->orderBy('order_number')->get()->groupBy('parent_id')
                    : [];
            } catch (\Throwable $e) {
                $menuItems = [];
            }

            $view->with('siteSettings', $siteSettings)
                ->with('menuItems', $menuItems);
        });

        // ─── Blade Permission Directive ─────────────────────────────────────
        // Usage: @permission('manage_roles') ... @endpermission
        //        @permission('view_sermons','manage_sermons') ... @endpermission
        Blade::if('permission', function (...$permissions) {
            $user = Auth::user();
            if (! $user || ! method_exists($user, 'isSuperAdmin')) {
                return false;
            }
            // Super admin has all permissions
            if ($user->isSuperAdmin()) {
                return true;
            }
            foreach ($permissions as $permission) {
                if (method_exists($user, 'hasPermission') && $user->hasPermission($permission)) {
                    return true;
                }
            }

            return false;
        });
    }
}
