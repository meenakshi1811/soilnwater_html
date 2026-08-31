<?php

namespace App\Http\Middleware;

use App\Support\ModulePermissions;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminOrModuleAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('web')->check() && Auth::guard('web')->user()?->isAdmin()) {
            Auth::shouldUse('web');

            return $next($request);
        }

        if (Auth::guard('employee')->check()) {
            $employee = Auth::guard('employee')->user();

            if (! $employee?->is_active) {
                Auth::guard('employee')->logout();

                $message = 'Your employee account is waiting for admin activation and a role assignment.';

                if ($request->expectsJson()) {
                    return response()->json(['message' => $message], 403);
                }

                return redirect()->route('employee.login')->withErrors(['email' => $message]);
            }

            Auth::shouldUse('employee');

            $module = ModulePermissions::moduleForAdminRoute($request->route()?->getName());

            if ($module && $employee->canModule($module, 'read')) {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'You do not have permission to access this area.'], 403);
            }

            abort(403, 'You do not have permission to access this area.');
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
