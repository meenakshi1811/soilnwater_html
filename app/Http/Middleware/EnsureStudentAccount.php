<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isStudent()) {
            abort(403, 'This area is only available to child accounts.');
        }

        $user->loadMissing('childProfile');

        if (! $user->childProfile?->isApproved()) {
            abort(403, 'Your child profile is not approved yet.');
        }

        return $next($request);
    }
}
