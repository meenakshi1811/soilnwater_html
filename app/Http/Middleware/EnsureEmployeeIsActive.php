<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $employee = Auth::guard('employee')->user();

        if (! $employee) {
            return redirect()->guest(route('employee.login'));
        }

        if (! $employee->is_active) {
            Auth::guard('employee')->logout();

            $message = 'Your employee account is waiting for admin activation and a role assignment.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()
                ->route('employee.login')
                ->withErrors(['email' => $message]);
        }

        Auth::shouldUse('employee');

        return $next($request);
    }
}
