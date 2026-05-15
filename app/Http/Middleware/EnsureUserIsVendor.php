<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVendor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'vendor') {
            abort(403, 'Vendor access only.');
        }

        $vendor = $user->vendor;

        if (! $vendor) {
            abort(403, 'Vendor profile not found.');
        }

        if (! $vendor->isApproved()) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Your vendor account is pending admin approval. You will be notified once approved.',
            ]);
        }

        return $next($request);
    }
}
