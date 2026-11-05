<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCollegeAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'college') {
            abort(403, 'College access only.');
        }

        if (! $user->institute) {
            abort(403, 'College profile not found.');
        }

        return $next($request);
    }
}
