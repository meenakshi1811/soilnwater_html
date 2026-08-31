<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureWebVerifiedOrEmployee
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::guard('employee')->check()) {
            Auth::shouldUse('employee');

            return $next($request);
        }

        $user = Auth::guard('web')->user();

        if ($user) {
            if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            Auth::shouldUse('web');

            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
