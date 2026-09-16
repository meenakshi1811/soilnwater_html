<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsSchool
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'school') {
            abort(403, 'School access only.');
        }

        $institute = $user->institute;

        if (! $institute) {
            abort(403, 'School profile not found.');
        }

        if (! $institute->isApproved()) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Your school account is pending admin approval. You will be notified once approved.',
            ]);
        }

        return $next($request);
    }
}
