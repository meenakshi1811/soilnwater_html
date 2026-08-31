<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use App\Support\AuthActor;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['backend.layouts.app', 'backend.partials.sidebar', 'backend.partials.sidebar-module-menus'], function (): void {
            AuthActor::shouldUseActiveGuard();
        });

        Paginator::defaultView('vendor.pagination.bootstrap-5');
        Paginator::defaultSimpleView('vendor.pagination.simple-bootstrap-5');

        if (! $this->app->runningInConsole()) {
            $request = request();

            if ($request->isSecure() || $request->header('X-Forwarded-Proto') === 'https') {
                URL::forceScheme('https');
            } elseif (! $this->app->environment('local')) {
                URL::forceScheme('https');
            }
        }
    }
}
