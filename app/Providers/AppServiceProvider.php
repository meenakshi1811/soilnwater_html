<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use App\Support\AuthActor;
use App\Support\ManagedEducator;
use App\Support\ManagedInstitute;
use App\Support\SchoolInstituteHelper;

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

        View::composer('backend.institute.*', function ($view): void {
            // An admin managing a school from the admin panel: the same pages, on the admin's routes
            $managed = ManagedInstitute::fromAdmin();
            if ($managed) {
                $owner = $managed->user;
                $view->with([
                    'portalPrefix' => $owner?->portalRoutePrefix() ?? 'school',
                    'portalLabel' => \App\Support\SchoolInstituteHelper::label($owner?->role),
                    'portalRoute' => static fn (string $suffix, mixed $parameters = []) => ManagedInstitute::adminRoute($managed, $suffix, $parameters),
                    'managedInstitute' => $managed,
                ]);

                return;
            }

            $user = AuthActor::user();
            $prefix = $user?->portalRoutePrefix() ?? 'school';
            $view->with([
                'portalPrefix' => $prefix,
                'portalLabel' => \App\Support\SchoolInstituteHelper::label($user?->role),
                'portalRoute' => static fn (string $suffix, mixed $parameters = []) => $user
                    ? $user->portalRoute($suffix, $parameters)
                    : SchoolInstituteHelper::routeForPrefix($prefix, $suffix, $parameters),
                'managedInstitute' => null,
            ]);
        });

        View::composer(['backend.educator.profile', 'backend.educator.tuition'], function ($view): void {
            // An admin managing a teacher / tutor from the admin panel: the same pages, on the admin's routes
            $managed = ManagedEducator::fromAdmin();
            if ($managed) {
                $view->with([
                    'educatorRoute' => static fn (string $suffix, mixed $parameters = []) => ManagedEducator::adminRoute($managed, $suffix, $parameters),
                    'managedEducator' => $managed,
                ]);

                return;
            }

            $view->with([
                'educatorRoute' => static fn (string $suffix, mixed $parameters = []) => route('educator.'.$suffix, $parameters),
                'managedEducator' => null,
            ]);
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
