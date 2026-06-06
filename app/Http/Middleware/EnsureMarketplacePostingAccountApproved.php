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

        if ($user?->isVendor()) {
            if ($user->vendor?->isApproved()) {
                return $next($request);
            }

            $message = 'Your vendor account must be approved by admin before you can post ads or offers.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->route('vendor.pending')->with('status', $message);
        }

        if ($user?->isConsultant()) {
            if ($user->consultant?->isApproved()) {
                return $next($request);
            }

            $message = 'Your consultant account must be approved by admin before you can post ads or offers.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->route('consultant.pending')->with('status', $message);
        }

        if ($user?->isServiceProvider()) {
            if ($user->serviceProvider?->isApproved()) {
                return $next($request);
            }

            $message = 'Your service account must be approved by admin before you can post ads or offers.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 403);
            }

            return redirect()->route('service_provider.pending')->with('status', $message);
        }

        return $next($request);
    }
}
