<?php

// API for the mobile app (prefix /api). No CSRF token is needed here.

use App\Http\Controllers\Api\DeviceTokenController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

// Push notification tokens. The website's session is read (not required), so a token sent from
// the app's WebView while someone is logged in is linked to that user.
Route::middleware([EncryptCookies::class, AddQueuedCookiesToResponse::class, StartSession::class, 'throttle:60,1'])
    ->group(function (): void {
        Route::post('/device-tokens', [DeviceTokenController::class, 'store'])->name('api.device-tokens.store');
        Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy'])->name('api.device-tokens.destroy');
    });
