<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudyMaterialPublisher
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user?->canPublishStudyMaterials()) {
            abort(403, 'You are not allowed to publish study materials.');
        }

        return $next($request);
    }
}
