<?php

namespace App\Http\Middleware;

use App\Support\ActiveChildSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureChildPortalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $activeChildProfile = ActiveChildSession::profile();

        if ($activeChildProfile && $activeChildProfile->parent_user_id === $user->id && $activeChildProfile->isApproved()) {
            $request->attributes->set('activeChildProfile', $activeChildProfile);

            return $next($request);
        }

        if ($user->isSelfRegisteredStudent()) {
            return $next($request);
        }

        if ($user->isStudent()) {
            $user->loadMissing('childProfile');

            if ($user->childProfile?->isApproved()) {
                return $next($request);
            }
        }

        abort(403, 'This area is only available through an approved child profile.');
    }
}
