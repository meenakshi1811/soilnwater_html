<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'frontend.index')->name('frontend.index');

Auth::routes(['verify' => true]);

Route::middleware('guest')->group(function () {
    Route::post('/login/otp/send', [LoginController::class, 'sendOtp'])->name('login.otp.send');
    Route::get('/login/otp', [LoginController::class, 'showOtpForm'])->name('login.otp.form');
    Route::post('/login/otp/verify', [LoginController::class, 'verifyOtp'])->name('login.otp.verify');
    Route::post('/login/verification/resend', [LoginController::class, 'resendVerification'])->name('login.verification.resend');
    Route::get('/auth/google', [LoginController::class, 'googleLogin'])->name('login.google');
});

Route::get('/home', [HomeController::class, 'index'])
    ->middleware('verified')
    ->name('home');
