<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeOrAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
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

            return $next($request);
        }

        if (Auth::guard('web')->check() && Auth::guard('web')->user()?->isAdmin()) {
            Auth::shouldUse('web');

            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('employee.login'));
    }
}
