<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotInstalled
{
    /**
     * If the application is NOT installed yet, redirect to the standalone installer.
     * Skips install.php and asset URLs to avoid redirect loops.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip the check for the standalone installer, install routes, and assets
        if ($request->is('install.php*') || $request->is('install*') || $request->is('admin/assets*') || $request->is('admin/logo*') || $request->is('favicon*') || $request->is('diagnostic.php*')) {
            return $next($request);
        }

        if (! self::alreadyInstalled()) {
            return redirect()->to('/install.php');
        }

        return $next($request);
    }

    /**
     * Determine if the application has been installed.
     */
    public static function alreadyInstalled(): bool
    {
        // Primary check: look for the installed flag file
        if (file_exists(storage_path('installed'))) {
            return true;
        }

        // Secondary check: try to connect to the database and look for users
        try {
            $connection = config('database.default');
            if ($connection !== 'sqlite') {
                $database = config("database.connections.{$connection}.database");
                if ($database && $database !== 'laravel') {
                    // Check the users table exists with a super admin
                    $hasUsers = DB::connection($connection)
                        ->table('users')
                        ->join('roles', 'users.role_id', '=', 'roles.id')
                        ->where('roles.is_super_admin', true)
                        ->exists();

                    if ($hasUsers) {
                        // Create the flag file for future checks
                        @file_put_contents(storage_path('installed'), date('Y-m-d H:i:s'));

                        return true;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Database not accessible — not installed
        }

        return false;
    }
}
