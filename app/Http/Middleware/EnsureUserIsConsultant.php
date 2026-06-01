<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsConsultant
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'consultant') {
            abort(403, 'Consultant access only.');
        }

        $consultant = $user->consultant;

        if (! $consultant) {
            abort(403, 'Consultant profile not found.');
        }

        if (! $consultant->isApproved()) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Your consultant account is pending admin approval. You will be notified once approved.',
            ]);
        }

        return $next($request);
    }
}
