<?php

namespace App\Http\Middleware;

use App\Models\Educator;
use App\Support\ManagedEducator;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin "manage" routes (admin/educators/{managedEducator}/manage/...) run the teacher / tutor
 * portal's own controllers on the educator in the address: it is kept on the request
 * (ManagedEducator) and taken out of the route parameters so those controllers get the same
 * arguments as in the portal.
 */
class ResolveManagedEducator
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $educator = Educator::query()->with('user')->find($route->parameter(ManagedEducator::ATTRIBUTE));

        abort_unless($educator && $educator->user?->role === 'teacher', 404);

        $request->attributes->set(ManagedEducator::ATTRIBUTE, $educator);
        $route->forgetParameter(ManagedEducator::ATTRIBUTE);

        return $next($request);
    }
}
