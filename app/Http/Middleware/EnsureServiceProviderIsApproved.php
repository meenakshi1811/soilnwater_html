<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureServiceProviderIsApproved
{
    /**
     * Allow service_provider role users who are logged in but may be pending (for profile completion messaging).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'service_provider') {
            abort(403, 'Service access only.');
        }

        if (! $user->serviceProvider) {
            abort(403, 'Service profile not found.');
        }

        return $next($request);
    }
}
