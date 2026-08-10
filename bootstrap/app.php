<?php

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\Cors;
use App\Http\Middleware\RedirectIfNotInstalled;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth' => Authenticate::class,
            'permission' => CheckPermission::class,
            'redirectifnotinstalled' => RedirectIfNotInstalled::class,
        ]);

        $middleware->api(prepend: [
            Cors::class,
            'throttle:api',
        ]);

        $middleware->web(append: [
            CheckMaintenanceMode::class,
            SecurityHeaders::class,
        ]);

        $middleware->web(prepend: [
            RedirectIfNotInstalled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->renderable(function (\Illuminate\Http\Exceptions\ThrottleRequestsException $e, Request $request) {
            if ($request->is('events/*/register')) {
                return back()->with('error', 'Too many registration attempts. Please wait a minute before trying again.');
            }

            return null;
        });
    })->create();
