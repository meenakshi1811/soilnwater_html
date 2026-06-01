<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureConsultantIsApproved
{
    /**
     * Allow consultant role users who are logged in but may be pending (for profile completion messaging).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'consultant') {
            abort(403, 'Consultant access only.');
        }

        if (! $user->consultant) {
            abort(403, 'Consultant profile not found.');
        }

        return $next($request);
    }
}
