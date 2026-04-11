<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsGeneralUser
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isGeneralUser()) {
            abort(403, 'This area is only available to user accounts.');
        }

        return $next($request);
    }
}
