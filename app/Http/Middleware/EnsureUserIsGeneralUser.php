<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsGeneralUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user?->isGeneralUser()) {
            return $next($request);
        }

        if ($user?->isStudent()) {
            return redirect()->route('child.dashboard');
        }

        if ($user?->isTeacher()) {
            return redirect()->route($user->educator?->isApproved() ? 'educator.dashboard' : 'educator.pending');
        }

        if ($user?->isParent()) {
            return redirect()->route($user->hasParentProfileEnabled() ? 'parent.dashboard' : 'parent.pending');
        }

        abort(403, 'This area is only available to user accounts.');
    }
}
