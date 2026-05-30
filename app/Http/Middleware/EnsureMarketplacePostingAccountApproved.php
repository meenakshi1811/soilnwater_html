<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMarketplacePostingAccountApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->isVendor()) {
            return $next($request);
        }

        if ($user->vendor?->isApproved()) {
            return $next($request);
        }

        $message = 'Your vendor account must be approved by admin before you can post ads or offers.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message], 403);
        }

        return redirect()->route('vendor.pending')->with('status', $message);
    }
}
