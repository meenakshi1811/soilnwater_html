<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'school') {
            abort(403, 'School access only.');
        }

        if (! $user->institute) {
            abort(403, 'School profile not found.');
        }

        return $next($request);
    }
}
