<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsServiceProvider
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'service_provider') {
            abort(403, 'Service access only.');
        }

        $service_provider = $user->serviceProvider;

        if (! $service_provider) {
            abort(403, 'Service profile not found.');
        }

        if (! $service_provider->isApproved()) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Your service account is pending admin approval. You will be notified once approved.',
            ]);
        }

        return $next($request);
    }
}
