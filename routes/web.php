<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\HomepageSettingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TermsAndConditionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Frontend\OfferPageController;
use App\Http\Controllers\Frontend\AdsMarketController;
use App\Http\Controllers\Frontend\AdReportController;
use App\Http\Controllers\Frontend\FrontendSearchController;
use App\Http\Controllers\Frontend\TermsAndConditionPageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ModuleAccessController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\Admin\PostOfferController;
use App\Http\Controllers\User\UserAdController;
use App\Http\Controllers\Admin\AdTemplateController;
use App\Http\Controllers\Admin\AdSubmissionController;
use App\Http\Controllers\Admin\AdSizeController;
use App\Http\Controllers\Admin\AdReportController as AdminAdReportController;
use App\Http\Controllers\Admin\ContactSupportController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\VendorProductApprovalController;
use App\Http\Controllers\Frontend\VendorStoreController;
use App\Http\Controllers\Vendor\VendorBranchController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorInquiryController;
use App\Http\Controllers\Vendor\VendorPendingController;
use App\Http\Controllers\Vendor\VendorPublicPageController;
use App\Http\Controllers\Vendor\VendorProfileController;
use App\Http\Controllers\Vendor\VendorProductController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [OfferPageController::class, 'home'])->name('frontend.index');
Route::get('/offers-market', [OfferPageController::class, 'index'])->name('frontend.offers.index');
Route::get('/offers-market/{offer}', [OfferPageController::class, 'show'])->name('frontend.offers.show');
Route::get('/vendors', [OfferPageController::class, 'vendors'])->name('frontend.vendors.index');
Route::get('/ads-market', [AdsMarketController::class, 'index'])->name('frontend.ads.index');
Route::get('/ads-market/{ad}', [AdsMarketController::class, 'show'])->name('frontend.ads.show');
Route::post('/ads-market/{ad}/report', [AdReportController::class, 'store'])->middleware(['auth', 'verified'])->name('frontend.ads.report');
Route::get('/search', [FrontendSearchController::class, 'index'])->name('frontend.search');
Route::view('/about-us', 'frontend.about')->name('frontend.about-us');
Route::post('/frontend/location', function (\Illuminate\Http\Request $request) {
    $data = $request->validate([
        'lat' => ['required', 'numeric', 'between:-90,90'],
        'lng' => ['required', 'numeric', 'between:-180,180'],
    ]);

    session(['frontend_lat' => (float) $data['lat'], 'frontend_lng' => (float) $data['lng']]);

    return response()->json(['ok' => true]);
})->name('frontend.location.store');

Route::get('/terms-and-condition/{moduleKey}', [TermsAndConditionPageController::class, 'show'])->name('frontend.terms.show');
Route::get('/privacy-policy', [TermsAndConditionPageController::class, 'privacyPolicy'])->name('frontend.privacy-policy');
Route::get('/cookie-policy', [TermsAndConditionPageController::class, 'cookiePolicy'])->name('frontend.cookie-policy');
Route::get('/store/{slug}', [VendorStoreController::class, 'show'])->name('store.show');
Route::get('/store/{slug}/products', [VendorStoreController::class, 'products'])->name('store.products.index');
Route::get('/store/{slug}/products/category/{category}', [VendorStoreController::class, 'categoryProducts'])->name('store.products.category');
Route::get('/store/{slug}/products/category/{category}/subcategory/{subcategory}', [VendorStoreController::class, 'subcategoryProducts'])->name('store.products.subcategory');
Route::get('/store/{slug}/products/{product}', [VendorStoreController::class, 'productShow'])->name('store.products.show');
Route::get('/store/{slug}/about', [VendorStoreController::class, 'about'])->name('store.about');
Route::get('/store/{slug}/contact', [VendorStoreController::class, 'contact'])->name('store.contact');
Route::post('/store/{slug}/enquiry', [VendorStoreController::class, 'sendGeneralInquiry'])->name('store.enquiry');
Route::post('/store/{slug}/products/{product}/enquiry', [VendorStoreController::class, 'sendInquiry'])->name('store.products.enquiry');
Route::post('/vendor-enquiry', [UserAdController::class, 'vendorEnquiry'])->name('frontend.vendor-enquiry');

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

    Route::get('/vendor/pending', [VendorPendingController::class, 'show'])->name('vendor.pending');

    Route::prefix('vendor')->name('vendor.')->middleware(['vendor.account'])->group(function () {
        Route::get('/dashboard', [VendorDashboardController::class, 'dashboard'])->middleware('vendor')->name('dashboard');
        Route::get('/branches', [VendorBranchController::class, 'index'])->middleware('vendor')->name('branches.index');
        Route::get('/branches/create', [VendorBranchController::class, 'create'])->middleware('vendor')->name('branches.create');
        Route::post('/branches', [VendorBranchController::class, 'store'])->middleware('vendor')->name('branches.store');
        Route::get('/branches/{branch}/edit', [VendorBranchController::class, 'edit'])->middleware('vendor')->name('branches.edit');
        Route::put('/branches/{branch}', [VendorBranchController::class, 'update'])->middleware('vendor')->name('branches.update');
        Route::delete('/branches/{branch}', [VendorBranchController::class, 'destroy'])->middleware('vendor')->name('branches.destroy');
        Route::get('/public-page', [VendorPublicPageController::class, 'edit'])->middleware('vendor')->name('public-page.edit');
        Route::put('/public-page', [VendorPublicPageController::class, 'update'])->middleware('vendor')->name('public-page.update');
        Route::get('/public-page/preview', [VendorPublicPageController::class, 'preview'])->middleware('vendor')->name('public-page.preview');
        Route::delete('/banner-slides/{slide}', [VendorPublicPageController::class, 'deleteBannerSlide'])->middleware('vendor')->name('banner-slides.destroy');
        Route::resource('products', VendorProductController::class)->middleware('vendor');
        Route::get('/inquiries', [VendorInquiryController::class, 'index'])->middleware('vendor')->name('inquiries.index');
        Route::get('/profile', [VendorProfileController::class, 'edit'])->middleware('vendor')->name('profile.edit');
        Route::put('/profile', [VendorProfileController::class, 'update'])->middleware('vendor')->name('profile.update');
    });

    Route::prefix('user')->name('user.')->middleware('user')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [UserDashboardController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile', [UserDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::get('/post-ad', function () {
            return redirect()->to(route('frontend.index').'#post-ad');
        })->name('post-ad');
    });

    Route::prefix('dashboard/ads')->name('ads.')->group(function () {
        Route::get('/', [UserAdController::class, 'index'])->name('index');
        Route::get('/data', [UserAdController::class, 'data'])->name('data');
        Route::get('/categories/{category}/subcategories', [UserAdController::class, 'subcategories'])->name('categories.subcategories');
        Route::get('/create', [UserAdController::class, 'selectSize'])->name('create.size');
        Route::post('/request-customization', [UserAdController::class, 'requestCustomization'])->name('request-customization');
        Route::post('/contact-support', [UserAdController::class, 'contactSupport'])->name('contact-support');
        Route::get('/create/{sizeType}/customize', [UserAdController::class, 'customizeFromSize'])->name('create.customize.default');
        Route::post('/create/{sizeType}/pay', [UserAdController::class, 'markSizeAsPaid'])->name('create.size.pay');
        Route::get('/create/{sizeType}', [UserAdController::class, 'selectTemplate'])->name('create.template');
        Route::get('/create/{sizeType}/template/{template}', [UserAdController::class, 'customize'])->name('create.customize');
        Route::post('/create/{sizeType}', [UserAdController::class, 'store'])->name('store');
        Route::get('/view/{ad}', [UserAdController::class, 'show'])->name('show');
        Route::get('/{ad}/edit', [UserAdController::class, 'edit'])->name('edit');
        Route::put('/{ad}', [UserAdController::class, 'update'])->name('update');
        Route::delete('/{ad}', [UserAdController::class, 'destroy'])->name('destroy');
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
                Route::delete('/{ad}', [AdSubmissionController::class, 'destroy'])->name('destroy');
            });

            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [AdminAdReportController::class, 'index'])->name('index');
                Route::get('/data', [AdminAdReportController::class, 'data'])->name('data');
                Route::delete('/ad/{ad}', [AdminAdReportController::class, 'deleteAd'])->name('delete-ad');
            });

            Route::get('/contact-support', [ContactSupportController::class, 'index'])->name('contact-support.index');

            Route::prefix('sizes')->name('sizes.')->group(function () {
                Route::get('/', [AdSizeController::class, 'index'])->name('index');
                Route::get('/data', [AdSizeController::class, 'data'])->name('data');
                Route::get('/{size}', [AdSizeController::class, 'show'])->name('show');
                Route::post('/', [AdSizeController::class, 'store'])->name('store');
                Route::put('/{size}', [AdSizeController::class, 'update'])->name('update');
                Route::post('/{size}/status', [AdSizeController::class, 'updateStatus'])->name('status');
                Route::delete('/{size}', [AdSizeController::class, 'destroy'])->name('destroy');
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

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/data', [UserController::class, 'data'])->name('data');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
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

        Route::get('/homepage-settings', [HomepageSettingController::class, 'edit'])->name('homepage-settings.edit');
        Route::put('/homepage-settings', [HomepageSettingController::class, 'update'])->name('homepage-settings.update');

        Route::prefix('vendor-products')->name('vendor-products.')->group(function () {
            Route::get('/', [VendorProductApprovalController::class, 'index'])->name('index');
            Route::get('/data', [VendorProductApprovalController::class, 'data'])->name('data');
            Route::get('/all-products', [VendorProductApprovalController::class, 'allProductsIndex'])->name('all.index');
            Route::get('/all-products/data', [VendorProductApprovalController::class, 'allProductsData'])->name('all.data');
            Route::get('/{product}', [VendorProductApprovalController::class, 'show'])->name('show');
            Route::post('/{product}/approve', [VendorProductApprovalController::class, 'approve'])->name('approve');
            Route::post('/{product}/reject', [VendorProductApprovalController::class, 'reject'])->name('reject');
            Route::delete('/{product}', [VendorProductApprovalController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('vendors')->name('vendors.')->group(function () {
            Route::get('/', [VendorController::class, 'index'])->name('index');
            Route::get('/data', [VendorController::class, 'data'])->name('data');
            Route::get('/{vendor}', [VendorController::class, 'show'])->name('show');
            Route::get('/{vendor}/edit', [VendorController::class, 'edit'])->name('edit');
            Route::put('/{vendor}', [VendorController::class, 'update'])->name('update');
            Route::post('/{vendor}/approve', [VendorController::class, 'approve'])->name('approve');
            Route::post('/{vendor}/reject', [VendorController::class, 'reject'])->name('reject');
            Route::post('/{vendor}/toggle-premium', [VendorController::class, 'togglePremium'])->name('toggle-premium');
            Route::delete('/{vendor}', [VendorController::class, 'destroy'])->name('destroy');
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
