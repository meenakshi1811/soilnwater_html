<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ModuleAccessController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'frontend.index')->name('frontend.index');

Auth::routes(['verify' => true]);

Route::middleware('guest')->group(function () {
    Route::post('/login/otp/send', [LoginController::class, 'sendOtp'])->name('login.otp.send');
    Route::get('/login/otp', [LoginController::class, 'showOtpForm'])->name('login.otp.form');
    Route::post('/login/otp/verify', [LoginController::class, 'verifyOtp'])->name('login.otp.verify');
    Route::post('/login/verification/resend', [LoginController::class, 'resendVerification'])->name('login.verification.resend');
    Route::get('/auth/google', [LoginController::class, 'googleLogin'])->name('login.google');

    Route::get('/verification/contact', [RegisterController::class, 'showContactVerificationForm'])->name('register.contact.verify.form');
    Route::post('/verification/contact', [RegisterController::class, 'verifyContactOtp'])->name('register.contact.verify');
    Route::post('/verification/contact/resend', [RegisterController::class, 'resendContactOtp'])->name('register.contact.verify.resend');
    Route::get('/verification/contact/start', [RegisterController::class, 'startContactVerificationFromLogin'])->name('register.contact.verify.start');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/modules/{module}', [ModuleAccessController::class, 'show'])
        ->where('module', 'ecommerce|vendors|services|properties|builders|consultants|enquiry|products|user_enquiry')
        ->name('modules.show');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [AdminController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');

        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->name('index');
            Route::get('/data', [RoleController::class, 'data'])->name('data');
            Route::get('/options', [RoleController::class, 'listForSelect'])->name('options');
            Route::post('/', [RoleController::class, 'store'])->name('store');
            Route::get('/{role}', [RoleController::class, 'show'])->name('show');
            Route::put('/{role}', [RoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('employees')->name('employees.')->group(function () {
            Route::get('/', [EmployeeController::class, 'index'])->name('index');
            Route::get('/data', [EmployeeController::class, 'data'])->name('data');
            Route::post('/', [EmployeeController::class, 'store'])->name('store');
            Route::get('/{employee}', [EmployeeController::class, 'show'])->name('show');
            Route::put('/{employee}', [EmployeeController::class, 'update'])->name('update');
            Route::delete('/{employee}', [EmployeeController::class, 'destroy'])->name('destroy');
        });
    });
});
