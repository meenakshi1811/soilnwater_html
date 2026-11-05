<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsCollege
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'college') {
            abort(403, 'College access only.');
        }

        $institute = $user->institute;

        if (! $institute) {
            abort(403, 'College profile not found.');
        }

        if (! $institute->isApproved()) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Your college account is pending admin approval. You will be notified once approved.',
            ]);
        }

        return $next($request);
    }
}
