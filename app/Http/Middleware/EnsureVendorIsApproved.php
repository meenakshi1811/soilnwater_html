<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVendorIsApproved
{
    /**
     * Allow vendor role users who are logged in but may be pending (for profile completion messaging).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'vendor') {
            abort(403, 'Vendor access only.');
        }

        if (! $user->vendor) {
            abort(403, 'Vendor profile not found.');
        }

        return $next($request);
    }
}
