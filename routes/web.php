<?php

use App\Http\Controllers\Admin\AdReportController as AdminAdReportController;
use App\Http\Controllers\Admin\AdSizeController;
use App\Http\Controllers\Admin\AdSubmissionController;
use App\Http\Controllers\Admin\AdTemplateController;
use App\Http\Controllers\Admin\ApprovalCenterController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommunityPostApprovalController;
use App\Http\Controllers\Admin\CommunityPostReportController;
use App\Http\Controllers\Admin\ConsultantController;
use App\Http\Controllers\Admin\ConsultantServiceApprovalController;
use App\Http\Controllers\Admin\ContactSupportController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\HomepageSettingController;
use App\Http\Controllers\Admin\ListingPaymentSubmissionController;
use App\Http\Controllers\Admin\OfferReportController as AdminOfferReportController;
use App\Http\Controllers\Admin\OfferPriceController;
use App\Http\Controllers\Admin\PostOfferController;
use App\Http\Controllers\Admin\PremiumPaymentSubmissionController;
use App\Http\Controllers\Admin\PremiumPriceController;
use App\Http\Controllers\Admin\ProfileReportController as AdminProfileReportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\ServiceProviderController;
use App\Http\Controllers\Admin\ServiceProviderServiceApprovalController;
use App\Http\Controllers\Admin\TermsAndConditionController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\VendorProductApprovalController;
use App\Http\Controllers\Admin\VendorPublicPageController as AdminVendorPublicPageController;
use App\Http\Controllers\Admin\ConsultantPublicPageController as AdminConsultantPublicPageController;
use App\Http\Controllers\Admin\ServiceProviderPublicPageController as AdminServiceProviderPublicPageController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Community\CommunityAstroConsultancyEngagementController;
use App\Http\Controllers\Community\CommunityAuthorQuestionController;
use App\Http\Controllers\Community\CommunityAwarenessEngagementController;
use App\Http\Controllers\Community\CommunityBusinessEngagementController;
use App\Http\Controllers\Community\CommunityCommunityIssuesController;
use App\Http\Controllers\Community\CommunityEngagementController;
use App\Http\Controllers\Community\CommunityEnvironmentEngagementController;
use App\Http\Controllers\Community\CommunityLocalVoiceEngagementController;
use App\Http\Controllers\Community\CommunityPostController;
use App\Http\Controllers\Community\CommunityPostParticipationController;
use App\Http\Controllers\Community\CommunityReportEngagementController;
use App\Http\Controllers\Consultant\ConsultantBranchController;
use App\Http\Controllers\Consultant\ConsultantDashboardController;
use App\Http\Controllers\Consultant\ConsultantInquiryController;
use App\Http\Controllers\Consultant\ConsultantPendingController;
use App\Http\Controllers\Consultant\ConsultantProfileController;
use App\Http\Controllers\Consultant\ConsultantPublicPageController;
use App\Http\Controllers\Consultant\ConsultantServiceController;
use App\Http\Controllers\Discussion\DiscussionMemberController;
use App\Http\Controllers\Discussion\DiscussionPresenceController;
use App\Http\Controllers\Discussion\DiscussionReactionController;
use App\Http\Controllers\Discussion\DiscussionReplyController;
use App\Http\Controllers\Discussion\DiscussionReadController;
use App\Http\Controllers\Discussion\DiscussionTopicController;
use App\Http\Controllers\Frontend\AdReportController;
use App\Http\Controllers\Frontend\AdsMarketController;
use App\Http\Controllers\Frontend\ConsultantStoreController;
use App\Http\Controllers\Frontend\FrontendSearchController;
use App\Http\Controllers\Frontend\OfferPageController;
use App\Http\Controllers\Frontend\OfferReportController;
use App\Http\Controllers\Frontend\PremiumPageController;
use App\Http\Controllers\Frontend\PremiumPaymentController;
use App\Http\Controllers\Frontend\ProfileReportController;
use App\Http\Controllers\Frontend\ServiceProviderStoreController;
use App\Http\Controllers\Frontend\TermsAndConditionPageController;
use App\Http\Controllers\Frontend\VendorStoreController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ListingPaymentController;
use App\Http\Controllers\ModuleAccessController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ServiceProvider\ServiceProviderBranchController;
use App\Http\Controllers\ServiceProvider\ServiceProviderDashboardController;
use App\Http\Controllers\ServiceProvider\ServiceProviderInquiryController;
use App\Http\Controllers\ServiceProvider\ServiceProviderPendingController;
use App\Http\Controllers\ServiceProvider\ServiceProviderProfileController;
use App\Http\Controllers\ServiceProvider\ServiceProviderPublicPageController;
use App\Http\Controllers\ServiceProvider\ServiceProviderServiceController;
use App\Http\Controllers\User\UserAdController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\Vendor\VendorBranchController;
use App\Http\Controllers\Vendor\VendorDashboardController;
use App\Http\Controllers\Vendor\VendorInquiryController;
use App\Http\Controllers\Vendor\VendorPendingController;
use App\Http\Controllers\Vendor\VendorProductController;
use App\Http\Controllers\Vendor\VendorProfileController;
use App\Http\Controllers\Vendor\VendorPublicPageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [OfferPageController::class, 'home'])->name('frontend.index');
Route::get('/offers-market', [OfferPageController::class, 'index'])->name('frontend.offers.index');
Route::get('/offers-market/{offer}', [OfferPageController::class, 'show'])->name('frontend.offers.show');
Route::post('/offers-market/{offer}/report', [OfferReportController::class, 'store'])->middleware(['auth', 'verified'])->name('frontend.offers.report');
Route::get('/vendors', [OfferPageController::class, 'vendors'])->name('frontend.vendors.index');
Route::get('/consultants', [OfferPageController::class, 'consultants'])->name('frontend.consultants.index');
Route::get('/services', [OfferPageController::class, 'serviceProviders'])->name('frontend.service_providers.index');
Route::get('/ads-market', [AdsMarketController::class, 'index'])->name('frontend.ads.index');
Route::get('/ads-market/{ad}', [AdsMarketController::class, 'show'])->name('frontend.ads.show');
Route::post('/ads-market/{ad}/report', [AdReportController::class, 'store'])->middleware(['auth', 'verified'])->name('frontend.ads.report');
Route::post('/consultant/{consultant:slug}/report', [ProfileReportController::class, 'consultant'])->middleware(['auth', 'verified'])->name('consultant.report');
Route::post('/service/{service_provider:slug}/report', [ProfileReportController::class, 'serviceProvider'])->middleware(['auth', 'verified'])->name('service_provider.report');
Route::get('/search', [FrontendSearchController::class, 'index'])->name('frontend.search');
Route::get('/get-premium/{type}', [PremiumPageController::class, 'show'])
    ->whereIn('type', ['vendor', 'consultant', 'service'])
    ->name('frontend.premium.show');
Route::post('/get-premium/{type}/payment-confirmation', [PremiumPaymentController::class, 'store'])
    ->whereIn('type', ['vendor', 'consultant', 'service'])
    ->middleware(['auth', 'verified'])
    ->name('frontend.premium.payment.submit');
Route::view('/about-us', 'frontend.about')->name('frontend.about-us');
Route::view('/refund-policy', 'frontend.refund-policy')->name('frontend.refund-policy');
Route::view('/community-posting-policy', 'frontend.community-posting-policy')->name('frontend.community-posting-policy');

Route::get('/community', [CommunityPostController::class, 'index'])->name('community.index');
Route::redirect('/community/my-area', '/community?type=local-voices')->name('community.my-area.index');
Route::get('/community/community-issues', [CommunityCommunityIssuesController::class, 'index'])->name('community.community-issues.index');
Route::get('/community/community-issues/heat-map', [CommunityCommunityIssuesController::class, 'heatMapData'])->name('community.community-issues.heat-map');
Route::get('/auther/{uniqueName}', [CommunityPostController::class, 'author'])->name('community.authors.show');
Route::get('/community/{post:slug}', [CommunityPostController::class, 'show'])->name('community.show');
Route::post('/community/{post:slug}/share', [CommunityEngagementController::class, 'trackShare'])->name('community.share.track');
Route::post('/community/{post:slug}/awareness-engagement/volunteer', [CommunityAwarenessEngagementController::class, 'volunteer'])->name('community.awareness-engagement.volunteer');
Route::post('/community/{post:slug}/environment-engagement/volunteer', [CommunityEnvironmentEngagementController::class, 'volunteer'])->name('community.environment-engagement.volunteer');
Route::post('/community/{post:slug}/business-engagement/query', [CommunityBusinessEngagementController::class, 'submitQuery'])->name('community.business-engagement.query');
Route::post('/community/{post:slug}/astro-consultancy-engagement/private-query', [CommunityAstroConsultancyEngagementController::class, 'submitPrivateQuery'])->name('community.astro-consultancy-engagement.private-query');

Route::post('/frontend/location', function (Request $request) {
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
Route::post('/consultant-enquiry', [UserAdController::class, 'consultantEnquiry'])->name('frontend.consultant-enquiry');
Route::post('/service-enquiry', [UserAdController::class, 'serviceProviderEnquiry'])->name('frontend.service-provider-enquiry');
Route::post('/consultant/{slug}/services/{service}/enquiry', [ConsultantStoreController::class, 'sendServiceInquiry'])->name('consultant.services.enquiry');
Route::post('/consultant/{slug}/enquiry', [ConsultantStoreController::class, 'sendGeneralInquiry'])->name('consultant.enquiry');
Route::post('/service/{slug}/services/{service}/enquiry', [ServiceProviderStoreController::class, 'sendServiceInquiry'])->name('service_provider.services.enquiry');
Route::post('/service/{slug}/enquiry', [ServiceProviderStoreController::class, 'sendGeneralInquiry'])->name('service_provider.enquiry');

Auth::routes(['verify' => true]);

Route::middleware('guest')->group(function () {
    Route::post('/login/otp/send', [LoginController::class, 'sendOtp'])->name('login.otp.send');
    Route::get('/login/otp', [LoginController::class, 'showOtpForm'])->name('login.otp.form');
    Route::post('/login/otp/verify', [LoginController::class, 'verifyOtp'])->name('login.otp.verify');
    Route::post('/login/verification/resend', [LoginController::class, 'resendVerification'])->name('login.verification.resend');
    Route::get('/auth/google/login', [LoginController::class, 'googleLogin'])->name('login.google');
    Route::get('/auth/google/register', [LoginController::class, 'googleRegister'])->name('register.google');
    Route::get('/auth/google/callback', [LoginController::class, 'googleCallback'])->name('google.callback');
    Route::get('/auth/google/complete-profile', [LoginController::class, 'showGoogleCompleteProfile'])->name('register.google.complete');
    Route::post('/auth/google/complete-profile', [LoginController::class, 'storeGoogleCompleteProfile'])->name('register.google.complete.store');

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
    Route::prefix('discussions')->name('discussions.')->group(function () {
        Route::get('/users/search', [DiscussionMemberController::class, 'searchUsers'])->name('users.search');
        Route::get('/', [DiscussionTopicController::class, 'index'])->name('index');
        Route::get('/messenger/{topic?}', [DiscussionTopicController::class, 'messenger'])->name('messenger');
        Route::get('/unread-summary', [DiscussionReadController::class, 'summary'])->name('unread-summary');
        Route::post('/', [DiscussionTopicController::class, 'store'])->name('store');
        Route::get('/{topic}', [DiscussionTopicController::class, 'show'])->name('show');
        Route::get('/{topic}/online', [DiscussionPresenceController::class, 'show'])->name('online');
        Route::get('/{topic}/members', [DiscussionMemberController::class, 'index'])->name('members.index');
        Route::post('/{topic}/members', [DiscussionMemberController::class, 'store'])->name('members.store');
        Route::delete('/{topic}/members/{member}', [DiscussionMemberController::class, 'destroy'])->name('members.destroy');
        Route::post('/{topic}/group-image', [DiscussionTopicController::class, 'updateGroupImage'])->name('group-image.update');
        Route::delete('/{topic}/group-image', [DiscussionTopicController::class, 'destroyGroupImage'])->name('group-image.destroy');
        Route::patch('/{topic}/group-settings', [DiscussionTopicController::class, 'updateGroupSettings'])->name('group-settings.update');
        Route::delete('/{topic}/group', [DiscussionTopicController::class, 'destroyGroup'])->name('group.destroy');
        Route::post('/{topic}/leave', [DiscussionMemberController::class, 'leave'])->name('leave');
        Route::post('/{topic}/read', [DiscussionReadController::class, 'markRead'])->name('read');
        Route::post('/{topic}/replies', [DiscussionReplyController::class, 'store'])->name('replies.store');
        Route::post('/{topic}/pin', [DiscussionTopicController::class, 'pin'])->name('pin');
        Route::post('/{topic}/react', [DiscussionReactionController::class, 'reactToTopic'])->name('react');
        Route::post('/replies/{reply}/react', [DiscussionReactionController::class, 'reactToReply'])->name('replies.react');
    });
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::post('/dashboard/listing-payment', [ListingPaymentController::class, 'store'])
        ->middleware('marketplace.approved')
        ->name('listing.payment.submit');

    Route::prefix('dashboard/offers')->name('offers.')->group(function () {
        Route::get('/', [PostOfferController::class, 'offersIndex'])->name('index');
        Route::get('/data', [PostOfferController::class, 'offersData'])->name('data');
        Route::get('/{offer}/edit', [PostOfferController::class, 'edit'])->middleware('marketplace.approved')->name('edit');
        Route::get('/{offer}', [PostOfferController::class, 'show'])->name('show');
        Route::put('/{offer}/update-offer-status', [PostOfferController::class, 'updateOfferStatus'])->middleware('marketplace.approved')->name('update-offer-status');
        Route::post('/', [PostOfferController::class, 'store'])->middleware('marketplace.approved')->name('store');
        Route::put('/{offer}', [PostOfferController::class, 'update'])->middleware('marketplace.approved')->name('update');
        Route::delete('/{offer}', [PostOfferController::class, 'destroy'])->middleware('marketplace.approved')->name('destroy');
    });

    Route::get('dashboard/offers/categories/{category}/subcategories', [PostOfferController::class, 'subcategories'])
        ->middleware('marketplace.approved')
        ->name('offers.categories.subcategories');
    Route::get('/post-offer', [PostOfferController::class, 'index'])->middleware('marketplace.approved')->name('post-offer');

    Route::get('/modules/{module}', [ModuleAccessController::class, 'show'])
        ->where('module', 'ecommerce|vendors|services|properties|builders|consultants|service_providers|enquiry|products|offers|ads|user_enquiry')
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

    Route::get('/consultant/pending', [ConsultantPendingController::class, 'show'])->name('consultant.pending');

    Route::prefix('consultant')->name('consultant.')->middleware(['consultant.account'])->group(function () {
        Route::get('/dashboard', [ConsultantDashboardController::class, 'dashboard'])->middleware('consultant')->name('dashboard');
        Route::get('/branches', [ConsultantBranchController::class, 'index'])->middleware('consultant')->name('branches.index');
        Route::get('/branches/create', [ConsultantBranchController::class, 'create'])->middleware('consultant')->name('branches.create');
        Route::post('/branches', [ConsultantBranchController::class, 'store'])->middleware('consultant')->name('branches.store');
        Route::get('/branches/{branch}/edit', [ConsultantBranchController::class, 'edit'])->middleware('consultant')->name('branches.edit');
        Route::put('/branches/{branch}', [ConsultantBranchController::class, 'update'])->middleware('consultant')->name('branches.update');
        Route::delete('/branches/{branch}', [ConsultantBranchController::class, 'destroy'])->middleware('consultant')->name('branches.destroy');
        Route::get('/public-page', [ConsultantPublicPageController::class, 'edit'])->middleware('consultant')->name('public-page.edit');
        Route::put('/public-page', [ConsultantPublicPageController::class, 'update'])->middleware('consultant')->name('public-page.update');
        Route::get('/public-page/preview', [ConsultantPublicPageController::class, 'preview'])->middleware('consultant')->name('public-page.preview');
        Route::delete('/banner-slides/{slide}', [ConsultantPublicPageController::class, 'deleteBannerSlide'])->middleware('consultant')->name('banner-slides.destroy');
        Route::resource('services', ConsultantServiceController::class)->middleware('consultant');
        Route::get('/inquiries', [ConsultantInquiryController::class, 'index'])->middleware('consultant')->name('inquiries.index');
        Route::get('/profile', [ConsultantProfileController::class, 'edit'])->middleware('consultant')->name('profile.edit');
        Route::put('/profile', [ConsultantProfileController::class, 'update'])->middleware('consultant')->name('profile.update');
    });

    Route::get('/service/pending', [ServiceProviderPendingController::class, 'show'])->name('service_provider.pending');

    Route::prefix('service')->name('service_provider.')->middleware(['service_provider.account'])->group(function () {
        Route::get('/dashboard', [ServiceProviderDashboardController::class, 'dashboard'])->middleware('service_provider')->name('dashboard');
        Route::get('/branches', [ServiceProviderBranchController::class, 'index'])->middleware('service_provider')->name('branches.index');
        Route::get('/branches/create', [ServiceProviderBranchController::class, 'create'])->middleware('service_provider')->name('branches.create');
        Route::post('/branches', [ServiceProviderBranchController::class, 'store'])->middleware('service_provider')->name('branches.store');
        Route::get('/branches/{branch}/edit', [ServiceProviderBranchController::class, 'edit'])->middleware('service_provider')->name('branches.edit');
        Route::put('/branches/{branch}', [ServiceProviderBranchController::class, 'update'])->middleware('service_provider')->name('branches.update');
        Route::delete('/branches/{branch}', [ServiceProviderBranchController::class, 'destroy'])->middleware('service_provider')->name('branches.destroy');
        Route::get('/public-page', [ServiceProviderPublicPageController::class, 'edit'])->middleware('service_provider')->name('public-page.edit');
        Route::put('/public-page', [ServiceProviderPublicPageController::class, 'update'])->middleware('service_provider')->name('public-page.update');
        Route::get('/public-page/preview', [ServiceProviderPublicPageController::class, 'preview'])->middleware('service_provider')->name('public-page.preview');
        Route::delete('/banner-slides/{slide}', [ServiceProviderPublicPageController::class, 'deleteBannerSlide'])->middleware('service_provider')->name('banner-slides.destroy');
        Route::resource('services', ServiceProviderServiceController::class)->middleware('service_provider');
        Route::get('/inquiries', [ServiceProviderInquiryController::class, 'index'])->middleware('service_provider')->name('inquiries.index');
        Route::get('/profile', [ServiceProviderProfileController::class, 'edit'])->middleware('service_provider')->name('profile.edit');
        Route::put('/profile', [ServiceProviderProfileController::class, 'update'])->middleware('service_provider')->name('profile.update');
    });

    Route::prefix('user')->name('user.')->middleware('user')->group(function () {
        Route::get('/dashboard', [UserDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [UserDashboardController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile', [UserDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/convert-to-vendor', [UserDashboardController::class, 'convertToVendor'])->name('convert-to-vendor');
        Route::post('/convert-to-consultant', [UserDashboardController::class, 'convertToConsultant'])->name('convert-to-consultant');
        Route::post('/convert-to-service-provider', [UserDashboardController::class, 'convertToServiceProvider'])->name('convert-to-service-provider');
        Route::get('/post-ad', function () {
            return redirect()->to(route('frontend.index').'#post-ad');
        })->name('post-ad');
    });

    Route::post('/community/{post:slug}/react', [CommunityPostController::class, 'react'])->name('community.react');
    Route::post('/community/{post:slug}/rate', [CommunityPostController::class, 'rateStory'])->name('community.story.rate');
    Route::post('/community/{post:slug}/poll', [CommunityPostController::class, 'votePoll'])->name('community.poll.vote');
    Route::post('/community/{post:slug}/comments', [CommunityPostController::class, 'comment'])->name('community.comments.store');
    Route::post('/community/{post:slug}/comments/{comment}/approve', [CommunityPostController::class, 'approveComment'])->name('community.comments.approve');
    Route::post('/community/{post:slug}/save', [CommunityEngagementController::class, 'toggleSave'])->name('community.save.toggle');
    Route::post('/community/{post:slug}/participation/suggestion', [CommunityPostParticipationController::class, 'storeSuggestion'])->name('community.participation.suggestion');
    Route::post('/community/{post:slug}/participation/feedback', [CommunityPostParticipationController::class, 'storeFeedback'])->name('community.participation.feedback');
    Route::post('/community/{post:slug}/participation/evidence', [CommunityPostParticipationController::class, 'storeEvidence'])->name('community.participation.evidence');
    Route::post('/community/{post:slug}/report-engagement/support', [CommunityReportEngagementController::class, 'toggleSupport'])->name('community.report-engagement.support');
    Route::post('/community/{post:slug}/report-engagement/agree', [CommunityReportEngagementController::class, 'toggleAgree'])->name('community.report-engagement.agree');
    Route::post('/community/{post:slug}/report-engagement/follow', [CommunityReportEngagementController::class, 'toggleFollow'])->name('community.report-engagement.follow');
    Route::post('/community/{post:slug}/local-voice-engagement/support', [CommunityLocalVoiceEngagementController::class, 'toggleSupport'])->name('community.local-voice-engagement.support');
    Route::post('/community/{post:slug}/local-voice-engagement/follow', [CommunityLocalVoiceEngagementController::class, 'toggleFollow'])->name('community.local-voice-engagement.follow');
    Route::post('/community/{post:slug}/awareness-engagement/support', [CommunityAwarenessEngagementController::class, 'toggleSupport'])->name('community.awareness-engagement.support');
    Route::post('/community/{post:slug}/awareness-engagement/pledge', [CommunityAwarenessEngagementController::class, 'pledge'])->name('community.awareness-engagement.pledge');
    Route::post('/community/{post:slug}/environment-engagement/support', [CommunityEnvironmentEngagementController::class, 'toggleSupport'])->name('community.environment-engagement.support');
    Route::post('/community/{post:slug}/environment-engagement/follow', [CommunityEnvironmentEngagementController::class, 'toggleFollow'])->name('community.environment-engagement.follow');
    Route::post('/community/{post:slug}/report', [CommunityEngagementController::class, 'report'])->name('community.report');
    Route::post('/community/subscriptions/category', [CommunityEngagementController::class, 'toggleCategorySubscription'])->name('community.subscriptions.category.toggle');
    Route::post('/community/subscriptions/topic', [CommunityEngagementController::class, 'toggleTopicFollow'])->name('community.subscriptions.topic.toggle');
    Route::post('/community/{post:slug}/questions', [CommunityAuthorQuestionController::class, 'storeForPost'])->name('community.author-questions.store.post');
    Route::post('/community/authors/{author}/follow', [CommunityPostController::class, 'followAuthor'])->name('community.authors.follow');
    Route::post('/community/authors/{author}/questions', [CommunityAuthorQuestionController::class, 'storeForAuthor'])->name('community.author-questions.store.author');

    Route::prefix('dashboard/community-author-questions')->name('community.author-questions.')->group(function () {
        Route::get('/', [CommunityAuthorQuestionController::class, 'index'])->name('index');
        Route::post('/{question}/answer', [CommunityAuthorQuestionController::class, 'answer'])->name('answer');
    });

    Route::prefix('dashboard/community-posts')->name('community.posts.')->group(function () {
        Route::get('/', [CommunityPostController::class, 'myPosts'])->name('index');
        Route::get('/data', [CommunityPostController::class, 'myPostsData'])->name('data');
        Route::get('/create', [CommunityPostController::class, 'create'])->name('create');
        Route::patch('/author-url', [CommunityPostController::class, 'updateAuthorUrl'])->name('author-url.update');
        Route::post('/uploads/image', [CommunityPostController::class, 'uploadInlineImage'])->name('uploads.image');
        Route::post('/uploads/attachment', [CommunityPostController::class, 'uploadInlineAttachment'])->name('uploads.attachment');
        Route::post('/', [CommunityPostController::class, 'store'])->name('store');
        Route::get('/{post:slug}/manage', [CommunityPostController::class, 'authorShow'])->name('manage');
        Route::get('/{post:slug}', [CommunityPostController::class, 'show'])->name('show');
        Route::get('/{post:slug}/edit', [CommunityPostController::class, 'edit'])->name('edit');
        Route::put('/{post:slug}', [CommunityPostController::class, 'update'])->name('update');
        Route::delete('/{post:slug}', [CommunityPostController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('dashboard/community-saved')->name('community.saved.')->group(function () {
        Route::get('/', [CommunityEngagementController::class, 'savedPosts'])->name('index');
        Route::get('/data', [CommunityEngagementController::class, 'savedPostsData'])->name('data');
    });

    Route::get('/dashboard/community-subscriptions', [CommunityEngagementController::class, 'subscriptions'])->name('community.subscriptions.index');

    Route::prefix('dashboard/ads')->name('ads.')->group(function () {
        Route::get('/', [UserAdController::class, 'index'])->name('index');
        Route::get('/data', [UserAdController::class, 'data'])->name('data');
        Route::get('/categories/{category}/subcategories', [UserAdController::class, 'subcategories'])->middleware('marketplace.approved')->name('categories.subcategories');
        Route::get('/categories/by-modules/filter', [UserAdController::class, 'categoriesByModules'])->middleware('marketplace.approved')->name('categories.by-modules');
        Route::get('/create', [UserAdController::class, 'selectSize'])->middleware('marketplace.approved')->name('create.size');
        Route::post('/request-customization', [UserAdController::class, 'requestCustomization'])->middleware('marketplace.approved')->name('request-customization');
        Route::post('/contact-support', [UserAdController::class, 'contactSupport'])->middleware('marketplace.approved')->name('contact-support');
        Route::get('/create/{sizeType}/customize', [UserAdController::class, 'customizeFromSize'])->middleware('marketplace.approved')->name('create.customize.default');
        Route::post('/create/{sizeType}/pay', [UserAdController::class, 'markSizeAsPaid'])->middleware('marketplace.approved')->name('create.size.pay');
        Route::get('/create/{sizeType}', [UserAdController::class, 'selectTemplate'])->middleware('marketplace.approved')->name('create.template');
        Route::get('/create/{sizeType}/template/{template}', [UserAdController::class, 'customize'])->middleware('marketplace.approved')->name('create.customize');
        Route::post('/create/{sizeType}', [UserAdController::class, 'store'])->middleware('marketplace.approved')->name('store');
        Route::get('/view/{ad}', [UserAdController::class, 'show'])->name('show');
        Route::get('/{ad}/edit', [UserAdController::class, 'edit'])->middleware('marketplace.approved')->name('edit');
        Route::put('/{ad}', [UserAdController::class, 'update'])->middleware('marketplace.approved')->name('update');
        Route::delete('/{ad}', [UserAdController::class, 'destroy'])->middleware('marketplace.approved')->name('destroy');
        Route::get('/{ad}', [UserAdController::class, 'show'])->name('legacy.show');
    });

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [AdminController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile', [AdminController::class, 'updateProfile'])->name('profile.update');

        Route::prefix('approvals')->name('approvals.')->group(function () {
            Route::get('/', [ApprovalCenterController::class, 'index'])->name('index');
            Route::get('/data', [ApprovalCenterController::class, 'data'])->name('data');
            Route::post('/{type}/{id}/approve', [ApprovalCenterController::class, 'approve'])->name('approve');
            Route::post('/{type}/{id}/decline', [ApprovalCenterController::class, 'decline'])->name('decline');
        });

        Route::prefix('premium-payments')->name('premium-payments.')->group(function () {
            Route::get('/', [PremiumPaymentSubmissionController::class, 'index'])->name('index');
            Route::get('/data', [PremiumPaymentSubmissionController::class, 'data'])->name('data');
            Route::get('/{submission}', [PremiumPaymentSubmissionController::class, 'show'])->name('show');
            Route::post('/{submission}/approve', [PremiumPaymentSubmissionController::class, 'approve'])->name('approve');
            Route::post('/{submission}/reject', [PremiumPaymentSubmissionController::class, 'reject'])->name('reject');
        });

        Route::prefix('premium-prices')->name('premium-prices.')->group(function () {
            Route::get('/', [PremiumPriceController::class, 'index'])->name('index');
            Route::put('/{premiumPrice}', [PremiumPriceController::class, 'update'])->name('update');
        });

        Route::prefix('listing-payments')->name('listing-payments.')->group(function () {
            Route::get('/', [ListingPaymentSubmissionController::class, 'index'])->name('index');
            Route::get('/data', [ListingPaymentSubmissionController::class, 'data'])->name('data');
            Route::get('/{submission}', [ListingPaymentSubmissionController::class, 'show'])->name('show');
            Route::post('/{submission}/approve', [ListingPaymentSubmissionController::class, 'approve'])->name('approve');
            Route::post('/{submission}/reject', [ListingPaymentSubmissionController::class, 'reject'])->name('reject');
        });

        Route::prefix('community-posts')->name('community-posts.')->group(function () {
            Route::get('/', [CommunityPostApprovalController::class, 'index'])->name('index');
            Route::get('/data', [CommunityPostApprovalController::class, 'data'])->name('data');
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [CommunityPostReportController::class, 'index'])->name('index');
                Route::get('/data', [CommunityPostReportController::class, 'data'])->name('data');
                Route::delete('/post/{post}', [CommunityPostReportController::class, 'deletePost'])->name('delete-post');
            });
            Route::get('/all', [CommunityPostApprovalController::class, 'allIndex'])->name('all.index');
            Route::get('/all/data', [CommunityPostApprovalController::class, 'allData'])->name('all.data');
            Route::get('/{post}/preview', [CommunityPostApprovalController::class, 'preview'])->name('preview');
            Route::get('/{post}', [CommunityPostApprovalController::class, 'show'])->name('show');
            Route::post('/{post}/approve', [CommunityPostApprovalController::class, 'approve'])->name('approve');
            Route::post('/{post}/reject', [CommunityPostApprovalController::class, 'reject'])->name('reject');
            Route::post('/{post}/decline', [CommunityPostApprovalController::class, 'decline'])->name('decline');
            Route::post('/{post}/draft', [CommunityPostApprovalController::class, 'moveToDraft'])->name('draft');
            Route::post('/{post}/archive', [CommunityPostApprovalController::class, 'archive'])->name('archive');
            Route::post('/{post}/feature', [CommunityPostApprovalController::class, 'feature'])->name('feature');
            Route::post('/{post}/sponsor', [CommunityPostApprovalController::class, 'sponsor'])->name('sponsor');
            Route::post('/{post}/highlight', [CommunityPostApprovalController::class, 'highlight'])->name('highlight');
            Route::post('/{post}/quality-score', [CommunityPostApprovalController::class, 'updateQualityScore'])->name('quality-score');
            Route::post('/{post}/recalculate-score', [CommunityPostApprovalController::class, 'recalculateScore'])->name('recalculate-score');
            Route::post('/{post}/article-badge', [CommunityPostApprovalController::class, 'toggleArticleBadge'])->name('article-badge');
        });

        Route::prefix('offers')->name('offers.')->group(function () {
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [AdminOfferReportController::class, 'index'])->name('index');
                Route::get('/data', [AdminOfferReportController::class, 'data'])->name('data');
                Route::delete('/offer/{offer}', [AdminOfferReportController::class, 'deleteOffer'])->name('delete-offer');
            });
        });

        Route::prefix('offer-prices')->name('offer-prices.')->group(function () {
            Route::get('/', [OfferPriceController::class, 'index'])->name('index');
            Route::post('/apply-all', [OfferPriceController::class, 'applyToAll'])->name('apply-all');
            Route::put('/{category}', [OfferPriceController::class, 'update'])->name('update');
        });

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
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::patch('/{user}/toggle-block', [UserController::class, 'toggleBlock'])->name('toggle-block');
            Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
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
            Route::get('/create', [VendorProductApprovalController::class, 'create'])->name('create');
            Route::post('/store', [VendorProductApprovalController::class, 'store'])->name('store');
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
            Route::get('/{vendor}/store-preview', [VendorController::class, 'storePreview'])->name('store-preview');
            Route::get('/{vendor}/public-page/edit', [AdminVendorPublicPageController::class, 'edit'])->name('public-page.edit');
            Route::put('/{vendor}/public-page', [AdminVendorPublicPageController::class, 'update'])->name('public-page.update');
            Route::get('/{vendor}/public-page/editor-preview', [AdminVendorPublicPageController::class, 'preview'])->name('public-page.editor-preview');
            Route::delete('/{vendor}/banner-slides/{slide}', [AdminVendorPublicPageController::class, 'destroyBannerSlide'])->name('banner-slides.destroy');
            Route::get('/{vendor}/public-page-review', [VendorController::class, 'reviewPublicPage'])->name('public-page.review');
            Route::get('/{vendor}/public-page-preview', [VendorController::class, 'previewPublicPage'])->name('public-page.preview');
            Route::post('/{vendor}/approve-public-page', [VendorController::class, 'approvePublicPage'])->name('approve-public-page');
            Route::post('/{vendor}/decline-public-page', [VendorController::class, 'declinePublicPage'])->name('decline-public-page');
            Route::post('/{vendor}/reject', [VendorController::class, 'reject'])->name('reject');
            Route::post('/{vendor}/toggle-premium', [VendorController::class, 'togglePremium'])->name('toggle-premium');
            Route::delete('/{vendor}', [VendorController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('consultant-services')->name('consultant-services.')->group(function () {
            Route::get('/', [ConsultantServiceApprovalController::class, 'index'])->name('index');
            Route::get('/data', [ConsultantServiceApprovalController::class, 'data'])->name('data');
            Route::get('/create', [ConsultantServiceApprovalController::class, 'create'])->name('create');
            Route::post('/store', [ConsultantServiceApprovalController::class, 'store'])->name('store');
            Route::get('/all-services', [ConsultantServiceApprovalController::class, 'allServicesIndex'])->name('all.index');
            Route::get('/all-services/data', [ConsultantServiceApprovalController::class, 'allServicesData'])->name('all.data');
            Route::get('/{service}', [ConsultantServiceApprovalController::class, 'show'])->name('show');
            Route::post('/{service}/approve', [ConsultantServiceApprovalController::class, 'approve'])->name('approve');
            Route::post('/{service}/reject', [ConsultantServiceApprovalController::class, 'reject'])->name('reject');
            Route::delete('/{service}', [ConsultantServiceApprovalController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('consultants')->name('consultants.')->group(function () {
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [AdminProfileReportController::class, 'consultants'])->name('index');
                Route::get('/data', [AdminProfileReportController::class, 'consultantData'])->name('data');
                Route::delete('/{consultant}', [AdminProfileReportController::class, 'deleteConsultant'])->name('delete-consultant');
            });
            Route::get('/', [ConsultantController::class, 'index'])->name('index');
            Route::get('/data', [ConsultantController::class, 'data'])->name('data');
            Route::get('/{consultant}', [ConsultantController::class, 'show'])->name('show');
            Route::get('/{consultant}/edit', [ConsultantController::class, 'edit'])->name('edit');
            Route::put('/{consultant}', [ConsultantController::class, 'update'])->name('update');
            Route::post('/{consultant}/approve', [ConsultantController::class, 'approve'])->name('approve');
            Route::get('/{consultant}/public-page/edit', [AdminConsultantPublicPageController::class, 'edit'])->name('public-page.edit');
            Route::put('/{consultant}/public-page', [AdminConsultantPublicPageController::class, 'update'])->name('public-page.update');
            Route::get('/{consultant}/public-page/editor-preview', [AdminConsultantPublicPageController::class, 'preview'])->name('public-page.editor-preview');
            Route::delete('/{consultant}/banner-slides/{slide}', [AdminConsultantPublicPageController::class, 'destroyBannerSlide'])->name('banner-slides.destroy');
            Route::get('/{consultant}/public-page-review', [ConsultantController::class, 'reviewPublicPage'])->name('public-page.review');
            Route::get('/{consultant}/public-page-preview', [ConsultantController::class, 'previewPublicPage'])->name('public-page.preview');
            Route::post('/{consultant}/approve-public-page', [ConsultantController::class, 'approvePublicPage'])->name('approve-public-page');
            Route::post('/{consultant}/decline-public-page', [ConsultantController::class, 'declinePublicPage'])->name('decline-public-page');
            Route::post('/{consultant}/reject', [ConsultantController::class, 'reject'])->name('reject');
            Route::post('/{consultant}/toggle-premium', [ConsultantController::class, 'togglePremium'])->name('toggle-premium');
            Route::delete('/{consultant}', [ConsultantController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('service-approvals')->name('service-provider-services.')->group(function () {
            Route::get('/', [ServiceProviderServiceApprovalController::class, 'index'])->name('index');
            Route::get('/data', [ServiceProviderServiceApprovalController::class, 'data'])->name('data');
            Route::get('/create', [ServiceProviderServiceApprovalController::class, 'create'])->name('create');
            Route::post('/store', [ServiceProviderServiceApprovalController::class, 'store'])->name('store');
            Route::get('/all-services', [ServiceProviderServiceApprovalController::class, 'allServicesIndex'])->name('all.index');
            Route::get('/all-services/data', [ServiceProviderServiceApprovalController::class, 'allServicesData'])->name('all.data');
            Route::get('/{service}', [ServiceProviderServiceApprovalController::class, 'show'])->name('show');
            Route::post('/{service}/approve', [ServiceProviderServiceApprovalController::class, 'approve'])->name('approve');
            Route::post('/{service}/reject', [ServiceProviderServiceApprovalController::class, 'reject'])->name('reject');
            Route::delete('/{service}', [ServiceProviderServiceApprovalController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('services')->name('service_providers.')->group(function () {
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/', [AdminProfileReportController::class, 'serviceProviders'])->name('index');
                Route::get('/data', [AdminProfileReportController::class, 'serviceProviderData'])->name('data');
                Route::delete('/{service_provider}', [AdminProfileReportController::class, 'deleteServiceProvider'])->name('delete-service-provider');
            });
            Route::get('/', [ServiceProviderController::class, 'index'])->name('index');
            Route::get('/data', [ServiceProviderController::class, 'data'])->name('data');
            Route::get('/{service_provider}', [ServiceProviderController::class, 'show'])->name('show');
            Route::get('/{service_provider}/edit', [ServiceProviderController::class, 'edit'])->name('edit');
            Route::put('/{service_provider}', [ServiceProviderController::class, 'update'])->name('update');
            Route::post('/{service_provider}/approve', [ServiceProviderController::class, 'approve'])->name('approve');
            Route::get('/{service_provider}/public-page/edit', [AdminServiceProviderPublicPageController::class, 'edit'])->name('public-page.edit');
            Route::put('/{service_provider}/public-page', [AdminServiceProviderPublicPageController::class, 'update'])->name('public-page.update');
            Route::get('/{service_provider}/public-page/editor-preview', [AdminServiceProviderPublicPageController::class, 'preview'])->name('public-page.editor-preview');
            Route::delete('/{service_provider}/banner-slides/{slide}', [AdminServiceProviderPublicPageController::class, 'destroyBannerSlide'])->name('banner-slides.destroy');
            Route::get('/{service_provider}/public-page-review', [ServiceProviderController::class, 'reviewPublicPage'])->name('public-page.review');
            Route::get('/{service_provider}/public-page-preview', [ServiceProviderController::class, 'previewPublicPage'])->name('public-page.preview');
            Route::post('/{service_provider}/approve-public-page', [ServiceProviderController::class, 'approvePublicPage'])->name('approve-public-page');
            Route::post('/{service_provider}/decline-public-page', [ServiceProviderController::class, 'declinePublicPage'])->name('decline-public-page');
            Route::post('/{service_provider}/reject', [ServiceProviderController::class, 'reject'])->name('reject');
            Route::post('/{service_provider}/toggle-premium', [ServiceProviderController::class, 'togglePremium'])->name('toggle-premium');
            Route::delete('/{service_provider}', [ServiceProviderController::class, 'destroy'])->name('destroy');
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

Route::get('/consultant/{slug}', [ConsultantStoreController::class, 'show'])->name('consultant.show');
Route::get('/consultant/{slug}/services', [ConsultantStoreController::class, 'services'])->name('consultant.public-services.index');
Route::get('/consultant/{slug}/services/category/{category}', [ConsultantStoreController::class, 'categoryServices'])->name('consultant.public-services.category');
Route::get('/consultant/{slug}/services/category/{category}/{subcategory}', [ConsultantStoreController::class, 'subcategoryServices'])->name('consultant.public-services.subcategory');
Route::get('/consultant/{slug}/about', [ConsultantStoreController::class, 'about'])->name('consultant.about');
Route::get('/consultant/{slug}/contact', [ConsultantStoreController::class, 'contact'])->name('consultant.contact');

Route::get('/service/{slug}', [ServiceProviderStoreController::class, 'show'])->name('service_provider.show');
Route::get('/service/{slug}/services', [ServiceProviderStoreController::class, 'services'])->name('service_provider.public-services.index');
Route::get('/service/{slug}/services/category/{category}', [ServiceProviderStoreController::class, 'categoryServices'])->name('service_provider.public-services.category');
Route::get('/service/{slug}/services/category/{category}/{subcategory}', [ServiceProviderStoreController::class, 'subcategoryServices'])->name('service_provider.public-services.subcategory');
Route::get('/service/{slug}/about', [ServiceProviderStoreController::class, 'about'])->name('service_provider.about');
Route::get('/service/{slug}/contact', [ServiceProviderStoreController::class, 'contact'])->name('service_provider.contact');
