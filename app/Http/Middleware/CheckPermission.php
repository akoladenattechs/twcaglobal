<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * Accept one or more permission names (OR logic).
     * If the user has ANY of the listed permissions, access is granted.
     * Super admin always passes.
     *
     * Usage: ->middleware('permission:manage_roles')
     *        ->middleware('permission:view_sermons,manage_sermons')
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403, 'Unauthenticated');
        }

        // Deactivated accounts are denied access (defense in depth)
        if ($user->status === 'inactive') {
            abort(403, 'Your account has been deactivated.');
        }

        // Super admin bypasses all permission checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Laravel passes the entire comma-separated string as one argument
        // so we need to split by comma
        $permList = [];
        foreach ($permissions as $arg) {
            foreach (explode(',', $arg) as $p) {
                $permList[] = trim($p);
            }
        }

        foreach ($permList as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'You do not have permission to access this page.');
    }
}
