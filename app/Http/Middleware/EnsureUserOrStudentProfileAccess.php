<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserOrStudentProfileAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isGeneralUser() && ! $user?->isStudent()) {
            abort(403, 'This profile area is only available to user and student accounts.');
        }

        return $next($request);
    }
}
