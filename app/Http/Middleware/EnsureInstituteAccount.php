<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstituteAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'institute') {
            abort(403, 'School / Institute access only.');
        }

        if (! $user->institute) {
            abort(403, 'Institute profile not found.');
        }

        return $next($request);
    }
}
