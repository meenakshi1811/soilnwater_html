<?php

namespace App\Http\Middleware;

use App\Models\Institute;
use App\Support\ManagedInstitute;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin "manage" routes (admin/schools/{managedInstitute}/manage/...) run the school portal's own
 * controllers on the school in the address: it is kept on the request (ManagedInstitute) and taken
 * out of the route parameters so those controllers get the same arguments as in the portal.
 */
class ResolveManagedInstitute
{
    public function handle(Request $request, Closure $next, string $ownerRole): Response
    {
        $route = $request->route();
        $institute = Institute::query()->with('user')->find($route->parameter(ManagedInstitute::ATTRIBUTE));

        abort_unless($institute && $institute->user?->role === $ownerRole, 404);

        $request->attributes->set(ManagedInstitute::ATTRIBUTE, $institute);
        $route->forgetParameter(ManagedInstitute::ATTRIBUTE);

        return $next($request);
    }
}
