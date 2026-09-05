<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEducatorAccount
{
    /**
     * Allow teacher/tutor role users who are logged in but may be pending.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['teacher', 'tutor'], true)) {
            abort(403, 'Teacher/Tutor access only.');
        }

        if (! $user->educator) {
            abort(403, 'Educator profile not found.');
        }

        return $next($request);
    }
}
