<?php

use App\Http\Middleware\EnsureConsultantIsApproved;
use App\Http\Middleware\EnsureMarketplacePostingAccountApproved;
use App\Http\Middleware\EnsureServiceProviderIsApproved;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsConsultant;
use App\Http\Middleware\EnsureUserIsGeneralUser;
use App\Http\Middleware\EnsureUserIsServiceProvider;
use App\Http\Middleware\EnsureUserIsVendor;
use App\Http\Middleware\EnsureVendorIsApproved;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'user' => EnsureUserIsGeneralUser::class,
            'vendor' => EnsureUserIsVendor::class,
            'consultant' => EnsureUserIsConsultant::class,
            'service_provider' => EnsureUserIsServiceProvider::class,
            'vendor.account' => EnsureVendorIsApproved::class,
            'consultant.account' => EnsureConsultantIsApproved::class,
            'service_provider.account' => EnsureServiceProviderIsApproved::class,
            'marketplace.approved' => EnsureMarketplacePostingAccountApproved::class,
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
