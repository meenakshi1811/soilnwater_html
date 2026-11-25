<?php

namespace App\Providers;

use App\Http\Middleware\PreventAuthenticatedPageCaching;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Support\ServiceProvider;

/**
 * Pages seen while signed in are not kept by the browser (no Back to a personal page after logout):
 * PreventAuthenticatedPageCaching runs on every web route.
 */
class PrivatePagesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Added to the HTTP kernel's "web" group (which keeps the router in step), whether the kernel
        // was created before this provider booted (a web request) or is created later
        $add = fn ($kernel) => $kernel->appendMiddlewareToGroup('web', PreventAuthenticatedPageCaching::class);

        if ($this->app->resolved(HttpKernel::class)) {
            $add($this->app->make(HttpKernel::class));
        } else {
            $this->app->afterResolving(HttpKernel::class, $add);
        }
    }
}
