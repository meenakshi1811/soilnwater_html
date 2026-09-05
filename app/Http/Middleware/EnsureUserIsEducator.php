<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsEducator
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'teacher') {
            abort(403, 'Teacher / Tutor access only.');
        }

        $educator = $user->educator;

        if (! $educator) {
            abort(403, 'Educator profile not found.');
        }

        if (! $educator->isApproved()) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Your '.$educator->roleLabel().' account is pending admin approval. You will be notified once approved.',
            ]);
        }

        return $next($request);
    }
}
