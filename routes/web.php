<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TermsAndConditionController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Frontend\OfferPageController;
use App\Http\Controllers\Frontend\TermsAndConditionPageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ModuleAccessController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\Admin\PostOfferController;
use App\Http\Controllers\User\UserAdController;
use App\Http\Controllers\Admin\AdTemplateController;
use App\Http\Controllers\Admin\AdSubmissionController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [OfferPageController::class, 'home'])->name('frontend.index');
Route::get('/offers-market', [OfferPageController::class, 'index'])->name('frontend.offers.index');
Route::get('/offers-market/{offer}', [OfferPageController::class, 'show'])->name('frontend.offers.show');
Route::view('/about-us', 'frontend.about')->name('frontend.about-us');
Route::get('/terms-and-condition/{moduleKey}', [TermsAndConditionPageController::class, 'show'])->name('frontend.terms.show');

Auth::routes(['verify' => true]);

Route::middleware('guest')->group(function () {
    Route::post('/login/otp/send', [LoginController::class, 'sendOtp'])->name('login.otp.send');
    Route::get('/login/otp', [LoginController::class, 'showOtpForm'])->name('login.otp.form');
    Route::post('/login/otp/verify', [LoginController::class, 'verifyOtp'])->name('login.otp.verify');
    Route::post('/login/verification/resend', [LoginController::class, 'resendVerification'])->name('login.verification.resend');
    Route::get('/auth/google/login', [LoginController::class, 'googleLogin'])->name('login.google');
    Route::get('/auth/google/register', [LoginController::class, 'googleRegister'])->name('register.google');
    Route::get('/auth/google/callback', [LoginController::class, 'googleCallback'])->name('google.callback');

    Route::get('/verification/contact', [RegisterController::class, 'showContactVerificationForm'])->name('register.contact.verify.form');
    Route::post('/verification/contact', [RegisterController::class, 'verifyContactOtp'])->name('register.contact.verify');
    Route::post('/verification/contact/resend', [RegisterController::class, 'resendContactOtp'])->name('register.contact.verify.resend');
    Route::get('/verification/contact/start', [RegisterController::class, 'startContactVerificationFromLogin'])->name('register.contact.verify.start');
    Route::get('/verification/phone/start', [RegisterController::class, 'startPhoneVerification'])->name('register.phone.verify.start');
    Route::get('/verification/phone', [RegisterController::class, 'showPhoneVerificationForm'])->name('register.phone.verify.form');
    Route::post('/verification/phone/send', [RegisterController::class, 'sendPhoneVerificationOtp'])->name('register.phone.verify.send');
    Route::post('/verification/phone/verify', [RegisterController::class, 'verifyPhoneOtp'])->name('register.phone.verify');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::prefix('dashboard/offers')->name('offers.')->group(function () {
        Route::get('/', [PostOfferController::class, 'offersIndex'])->name('index');
        Route::get('/data', [PostOfferController::class, 'offersData'])->name('data');
        Route::get('/{offer}/edit', [PostOfferController::class, 'edit'])->name('edit');
        Route::get('/{offer}', [PostOfferController::class, 'show'])->name('show');
        Route::put('/{offer}/update-offer-status', [PostOfferController::class, 'updateOfferStatus'])->name('update-offer-status');
        Route::post('/', [PostOfferController::class, 'store'])->name('store');
        Route::put('/{offer}', [PostOfferController::class, 'update'])->name('update');
        Route::delete('/{offer}', [PostOfferController::class, 'destroy'])->name('destroy');
    });

    Route::get('dashboard/offers/categories/{category}/subcategories', [PostOfferController::class, 'subcategories'])
        ->name('offers.categories.subcategories');
    Route::get('/post-offer', [PostOfferController::class, 'index'])->name('post-offer');

    Route::get('/modules/{module}', [ModuleAccessController::class, 'show'])
        ->where('module', 'ecommerce|vendors|services|properties|builders|consultants|enquiry|products|offers|ads|user_enquiry')
        ->name('modules.show');

    Route::prefix('user')->name('user.')->middleware('user')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [UserDashboardController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile', [UserDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::get('/post-ad', function () {
            return redirect()->to(route('frontend.index').'#post-ad');
        })->name('post-ad');
    });

    Route::prefix('dashboard/ads')->name('ads.')->middleware('user')->group(function () {
        Route::get('/', [UserAdController::class, 'index'])->name('index');
        Route::get('/data', [UserAdController::class, 'data'])->name('data');
        Route::get('/create', [UserAdController::class, 'selectSize'])->name('create.size');
        Route::get('/create/{sizeType}', [UserAdController::class, 'selectTemplate'])->name('create.template');
        Route::get('/create/{sizeType}/template/{template}', [UserAdController::class, 'customize'])->name('create.customize');
        Route::post('/create/{sizeType}/template/{template}', [UserAdController::class, 'store'])->name('store');
        Route::get('/view/{ad}', [UserAdController::class, 'show'])->name('show');
        Route::get('/{ad}', [UserAdController::class, 'show'])->name('legacy.show');
    });

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [AdminController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');

        Route::prefix('ads')->name('ads.')->group(function () {
            Route::prefix('templates')->name('templates.')->group(function () {
                Route::get('/', [AdTemplateController::class, 'index'])->name('index');
                Route::get('/data', [AdTemplateController::class, 'data'])->name('data');
                Route::get('/create', [AdTemplateController::class, 'create'])->name('create');
                Route::post('/', [AdTemplateController::class, 'store'])->name('store');
                Route::get('/{template}/edit', [AdTemplateController::class, 'edit'])->name('edit');
                Route::put('/{template}', [AdTemplateController::class, 'update'])->name('update');
            });

            Route::prefix('submissions')->name('submissions.')->group(function () {
                Route::get('/', [AdSubmissionController::class, 'index'])->name('index');
                Route::get('/data', [AdSubmissionController::class, 'data'])->name('data');
                Route::get('/{ad}', [AdSubmissionController::class, 'show'])->name('show');
                Route::post('/{ad}/approve', [AdSubmissionController::class, 'approve'])->name('approve');
                Route::post('/{ad}/reject', [AdSubmissionController::class, 'reject'])->name('reject');
            });
        });

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

        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('/data', [CategoryController::class, 'data'])->name('data');
            Route::get('/parents/options', [CategoryController::class, 'parentOptions'])->name('parents.options');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
            Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('terms-and-conditions')->name('terms-and-conditions.')->group(function () {
            Route::get('/', [TermsAndConditionController::class, 'index'])->name('index');
            Route::get('/data', [TermsAndConditionController::class, 'data'])->name('data');
            Route::get('/modules', [TermsAndConditionController::class, 'moduleOptions'])->name('modules');
            Route::post('/', [TermsAndConditionController::class, 'store'])->name('store');
            Route::get('/{termsAndCondition}', [TermsAndConditionController::class, 'show'])->name('show');
            Route::put('/{termsAndCondition}', [TermsAndConditionController::class, 'update'])->name('update');
            Route::delete('/{termsAndCondition}', [TermsAndConditionController::class, 'destroy'])->name('destroy');
        });
    });
});
