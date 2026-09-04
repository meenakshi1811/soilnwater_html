<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Consultant;
use App\Models\HomepageSetting;
use App\Models\Offer;
use App\Models\ServiceProvider;
use App\Models\UserAd;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OfferPageController extends Controller
{
    public function home(): View
    {
        $lat = request()->filled('lat') ? (float) request()->input('lat') : session('frontend_lat');
        $lng = request()->filled('lng') ? (float) request()->input('lng') : session('frontend_lng');

        $offers = $this->baseOfferQuery(null, $lat, $lng)
            ->limit(10)
            ->get();

        $frontPageAdsQuery = UserAd::query()
            ->where('status', 'approved')
            ->whereIn('size_type', ['top_categories_ad_1', 'top_categories_ad_2', 'sponsored_listings_ad', 'below_sponsored_ad', 'ecommerce_ad', 'offer_discount_ad_1', 'offer_discount_ad_2', 'explore_products_ad', 'top_vendors_ad_1', 'top_vendors_ad_2', 'popular_greenwood_ad', 'popular_properties_ad', 'below_popular_ad', 'builders_developers_ad', 'below_builders_ad'])
            ->whereNotNull('final_image');

        if ($lat !== null && $lng !== null) {
            $frontPageAdsQuery
                ->select('user_ads.*')
                ->selectRaw('CASE WHEN location_lat IS NOT NULL AND location_lng IS NOT NULL THEN (6371 * acos(cos(radians(?)) * cos(radians(location_lat)) * cos(radians(location_lng) - radians(?)) + sin(radians(?)) * sin(radians(location_lat)))) ELSE NULL END as distance_km', [$lat, $lng, $lat])
                ->orderByRaw('CASE WHEN distance_km IS NULL THEN 1 ELSE 0 END')
                ->orderBy('distance_km');
        } else {
            $frontPageAdsQuery->latest('reviewed_at');
        }

        $frontPageAds = $frontPageAdsQuery
            ->latest('id')
            ->get(['id', 'title', 'size_type', 'final_image']);

        $recentApprovedAdsQuery = UserAd::query()
            ->with(['category:id,name'])
            ->where('status', 'approved')
            ->whereDoesntHave('adSize', fn (Builder $query) => $query->where('admin_only', true))
            ->where(function (Builder $query) {
                $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()->toDateString());
            })
            ->whereNotNull('final_image');

        if ($lat !== null && $lng !== null) {
            $recentApprovedAdsQuery
                ->select('user_ads.*')
                ->selectRaw('CASE WHEN location_lat IS NOT NULL AND location_lng IS NOT NULL THEN (6371 * acos(cos(radians(?)) * cos(radians(location_lat)) * cos(radians(location_lng) - radians(?)) + sin(radians(?)) * sin(radians(location_lat)))) ELSE NULL END as distance_km', [$lat, $lng, $lat])
                ->orderByRaw('CASE WHEN distance_km IS NULL THEN 1 ELSE 0 END')
                ->orderBy('distance_km');
        }

        $recentApprovedAds = $recentApprovedAdsQuery
            ->latest('created_at')
            ->latest('id')
            ->limit(20)
            ->get(['id', 'title', 'category_id', 'selected_category_ids', 'selected_modules', 'short_description', 'final_image', 'created_at']);

        $selectedCategoryNamesByRecentAdId = $this->resolveSelectedCategoryNamesByAdId($recentApprovedAds);

        $homepageSetting = HomepageSetting::query()->find(1);

        return view('frontend.index', [
            'offers' => $offers,
            'topCategoriesSliderAds' => $frontPageAds->where('size_type', 'top_categories_ad_1')->values(),
            'topSidebarSliderAds' => $frontPageAds->where('size_type', 'top_categories_ad_2')->values(),
            'sponsoredListingsAds' => $frontPageAds->where('size_type', 'sponsored_listings_ad')->values(),
            'belowSponsoredSliderAds' => $frontPageAds->where('size_type', 'below_sponsored_ad')->values(),
            'ecommerceSideSliderAds' => $frontPageAds->where('size_type', 'ecommerce_ad')->values(),
            'recentApprovedAds' => $recentApprovedAds,
            'selectedCategoryNamesByRecentAdId' => $selectedCategoryNamesByRecentAdId,
            'offerDiscountTopAds' => $frontPageAds->where('size_type', 'offer_discount_ad_1')->values(),
            'offerDiscountSideAds' => $frontPageAds->where('size_type', 'offer_discount_ad_2')->values(),
            'exploreProductsAds' => $frontPageAds->where('size_type', 'explore_products_ad')->values(),
            'topVendorsHeaderAds' => $frontPageAds->where('size_type', 'top_vendors_ad_1')->values(),
            'topVendorsSideAds' => $frontPageAds->where('size_type', 'top_vendors_ad_2')->values(),
            'popularGreenwoodAds' => $frontPageAds->where('size_type', 'popular_greenwood_ad')->values(),
            'popularPropertiesAds' => $frontPageAds->where('size_type', 'popular_properties_ad')->values(),
            'belowPopularAds' => $frontPageAds->where('size_type', 'below_popular_ad')->values(),
            'buildersDevelopersAds' => $frontPageAds->where('size_type', 'builders_developers_ad')->values(),
            'belowBuildersAds' => $frontPageAds->where('size_type', 'below_builders_ad')->values(),
            'topVendors' => $this->topVendorsQuery($lat, $lng)->limit(12)->get(),
            'topConsultants' => $this->topConsultantsQuery($lat, $lng)->limit(15)->get(),
            'topServiceProviders' => $this->topServiceProvidersQuery($lat, $lng)->limit(15)->get(),
            'vendorEnquiryCategories' => Category::query()
                ->whereNull('parent_id')
                ->forModule('vendors')
                ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
                ->orderBy('name')
                ->get(['id', 'name']),
            'consultantEnquiryCategories' => Category::query()
                ->whereNull('parent_id')
                ->whereJsonContains('modules', 'consultants')
                ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
                ->orderBy('name')
                ->get(['id', 'name']),
            'service_providerCategories' => Category::query()
                ->whereNull('parent_id')
                ->forModule('service_providers')
                ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
                ->orderBy('name')
                ->get(['id', 'name']),
            'hasLocation' => is_numeric($lat) && is_numeric($lng),
            'homepageSetting' => $homepageSetting,
        ]);
    }

    public function vendors(Request $request): View|JsonResponse
    {
        if (! $request->ajax() && ! $request->wantsJson() && $request->boolean('premium')) {
            return redirect()->route('frontend.vendors.premium', $request->query());
        }

        $listingData = $this->vendorListingPageData($request, 12);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->vendorListingJsonResponse($request, $listingData, includePremiumSection: true);
        }

        $premiumVendors = $this->topVendorsQuery($listingData['lat'], $listingData['lng'], $request)
            ->where('is_premium', true)
            ->limit(5)
            ->get();
        $premiumVendors->each->usePublishedPage();

        return view('frontend/vendors/index', [
            'vendors' => $listingData['vendors'],
            'premiumVendors' => $premiumVendors,
            'categories' => $listingData['categories'],
            'categoriesForFilter' => $listingData['categoriesForFilter'],
            'topCategories' => $this->topVendorCategories(),
            'vendorStats' => $this->vendorListingStats(),
            'hasLocation' => $listingData['hasLocation'],
            'homepageSetting' => HomepageSetting::query()->find(1),
        ]);
    }

    public function vendorListings(Request $request): View|JsonResponse
    {
        if (! $request->ajax() && ! $request->wantsJson() && $request->boolean('premium')) {
            return redirect()->route('frontend.vendors.premium', $request->query());
        }

        $listingData = $this->vendorListingPageData($request, 24);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->vendorListingJsonResponse($request, $listingData, includePremiumSection: false);
        }

        return view('frontend/vendors/listings', [
            'vendors' => $listingData['vendors'],
            'categories' => $listingData['categories'],
            'categoriesForFilter' => $listingData['categoriesForFilter'],
            'vendorStats' => $this->vendorListingStats(),
            'hasLocation' => $listingData['hasLocation'],
            'homepageSetting' => HomepageSetting::query()->find(1),
            'cardView' => $listingData['cardView'],
        ]);
    }

    public function premiumVendors(Request $request): View|JsonResponse
    {
        $lat = $request->filled('lat') ? (float) $request->input('lat') : session('frontend_lat');
        $lng = $request->filled('lng') ? (float) $request->input('lng') : session('frontend_lng');
        $hasLocation = is_numeric($lat) && is_numeric($lng);

        $categories = Category::query()
            ->whereNull('parent_id')
            ->forModule('vendors')
            ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
            ->orderBy('name')
            ->get(['id', 'name']);

        $categoriesForFilter = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'children' => $category->children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'parent_id' => $child->parent_id,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $vendors = $this->topVendorsQuery($lat, $lng, $request)
            ->where('is_premium', true)
            ->paginate(12)
            ->appends($request->query());
        $vendors->getCollection()->each->usePublishedPage();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('frontend.vendors.partials.premium-cards', [
                    'premiumVendors' => $vendors->getCollection(),
                    'hasLocation' => $hasLocation,
                ])->render(),
                'next_page_url' => $vendors->nextPageUrl(),
                'loaded_to' => $vendors->lastItem() ?? 0,
                'total' => $vendors->total(),
            ]);
        }

        return view('frontend/vendors/premium', [
            'vendors' => $vendors,
            'categories' => $categories,
            'categoriesForFilter' => $categoriesForFilter,
            'vendorStats' => $this->vendorListingStats(),
            'hasLocation' => $hasLocation,
            'homepageSetting' => HomepageSetting::query()->find(1),
        ]);
    }

    public function consultants(Request $request): View|JsonResponse
    {
        if (! $request->ajax() && ! $request->wantsJson() && $request->boolean('premium')) {
            return redirect()->route('frontend.consultants.premium', $request->query());
        }

        $listingData = $this->consultantListingPageData($request, 12);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->consultantListingJsonResponse($request, $listingData, includePremiumSection: true);
        }

        $premiumConsultants = $this->topConsultantsQuery($listingData['lat'], $listingData['lng'], $request)
            ->where('is_premium', true)
            ->limit(5)
            ->get();
        $premiumConsultants->each->usePublishedPage();

        return view('frontend/consultants/index', [
            'consultants' => $listingData['consultants'],
            'premiumConsultants' => $premiumConsultants,
            'categories' => $listingData['categories'],
            'categoriesForFilter' => $listingData['categoriesForFilter'],
            'topCategories' => $this->topConsultantCategories(),
            'consultantStats' => $this->consultantListingStats(),
            'hasLocation' => $listingData['hasLocation'],
            'homepageSetting' => HomepageSetting::query()->find(1),
        ]);
    }

    public function consultantListings(Request $request): View|JsonResponse
    {
        if (! $request->ajax() && ! $request->wantsJson() && $request->boolean('premium')) {
            return redirect()->route('frontend.consultants.premium', $request->query());
        }

        $listingData = $this->consultantListingPageData($request, 24);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->consultantListingJsonResponse($request, $listingData, includePremiumSection: false);
        }

        return view('frontend/consultants/listings', [
            'consultants' => $listingData['consultants'],
            'categories' => $listingData['categories'],
            'categoriesForFilter' => $listingData['categoriesForFilter'],
            'consultantStats' => $this->consultantListingStats(),
            'hasLocation' => $listingData['hasLocation'],
            'homepageSetting' => HomepageSetting::query()->find(1),
            'cardView' => $listingData['cardView'],
        ]);
    }

    public function premiumConsultants(Request $request): View|JsonResponse
    {
        $lat = $request->filled('lat') ? (float) $request->input('lat') : session('frontend_lat');
        $lng = $request->filled('lng') ? (float) $request->input('lng') : session('frontend_lng');
        $hasLocation = is_numeric($lat) && is_numeric($lng);
        $categories = Category::query()
            ->whereNull('parent_id')
            ->forModule('consultants')
            ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
            ->orderBy('name')
            ->get(['id', 'name']);
        $categoriesForFilter = $this->mapCategoriesForFilter($categories);

        $consultants = $this->topConsultantsQuery($lat, $lng, $request)
            ->where('is_premium', true)
            ->paginate(12)
            ->appends($request->query());
        $consultants->getCollection()->each->usePublishedPage();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('frontend.consultants.partials.premium-cards', [
                    'premiumConsultants' => $consultants->getCollection(),
                    'hasLocation' => $hasLocation,
                ])->render(),
                'next_page_url' => $consultants->nextPageUrl(),
                'loaded_to' => $consultants->lastItem() ?? 0,
                'total' => $consultants->total(),
            ]);
        }

        return view('frontend/consultants/premium', [
            'consultants' => $consultants,
            'categories' => $categories,
            'categoriesForFilter' => $categoriesForFilter,
            'consultantStats' => $this->consultantListingStats(),
            'hasLocation' => $hasLocation,
            'homepageSetting' => HomepageSetting::query()->find(1),
        ]);
    }

    public function consultantCategories(): View
    {
        return view('frontend/consultants/categories', [
            'categories' => $this->consultantCategoriesQuery()->get(),
            'consultantStats' => $this->consultantListingStats(),
        ]);
    }

    public function serviceProviders(Request $request): View|JsonResponse
    {
        if (! $request->ajax() && ! $request->wantsJson() && $request->boolean('premium')) {
            return redirect()->route('frontend.service_providers.premium', $request->query());
        }

        $listingData = $this->serviceProviderListingPageData($request, 12);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->serviceProviderListingJsonResponse($request, $listingData, includePremiumSection: true);
        }

        $premiumServiceProviders = $this->topServiceProvidersQuery($listingData['lat'], $listingData['lng'], $request)
            ->where('is_premium', true)
            ->limit(5)
            ->get();
        $premiumServiceProviders->each->usePublishedPage();

        return view('frontend/service_providers/index', [
            'service_providers' => $listingData['service_providers'],
            'premiumServiceProviders' => $premiumServiceProviders,
            'categories' => $listingData['categories'],
            'categoriesForFilter' => $listingData['categoriesForFilter'],
            'topCategories' => $this->topServiceProviderCategories(),
            'serviceProviderStats' => $this->serviceProviderListingStats(),
            'hasLocation' => $listingData['hasLocation'],
            'homepageSetting' => HomepageSetting::query()->find(1),
        ]);
    }

    public function serviceProviderListings(Request $request): View|JsonResponse
    {
        if (! $request->ajax() && ! $request->wantsJson() && $request->boolean('premium')) {
            return redirect()->route('frontend.service_providers.premium', $request->query());
        }

        $listingData = $this->serviceProviderListingPageData($request, 24);

        if ($request->ajax() || $request->wantsJson()) {
            return $this->serviceProviderListingJsonResponse($request, $listingData, includePremiumSection: false);
        }

        return view('frontend/service_providers/listings', [
            'service_providers' => $listingData['service_providers'],
            'categories' => $listingData['categories'],
            'categoriesForFilter' => $listingData['categoriesForFilter'],
            'serviceProviderStats' => $this->serviceProviderListingStats(),
            'hasLocation' => $listingData['hasLocation'],
            'homepageSetting' => HomepageSetting::query()->find(1),
            'cardView' => $listingData['cardView'],
        ]);
    }

    public function premiumServiceProviders(Request $request): View|JsonResponse
    {
        $lat = $request->filled('lat') ? (float) $request->input('lat') : session('frontend_lat');
        $lng = $request->filled('lng') ? (float) $request->input('lng') : session('frontend_lng');
        $hasLocation = is_numeric($lat) && is_numeric($lng);
        $categories = Category::query()
            ->whereNull('parent_id')
            ->forModule('service_providers')
            ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
            ->orderBy('name')
            ->get(['id', 'name']);
        $categoriesForFilter = $this->mapCategoriesForFilter($categories);

        $service_providers = $this->topServiceProvidersQuery($lat, $lng, $request)
            ->where('is_premium', true)
            ->paginate(12)
            ->appends($request->query());
        $service_providers->getCollection()->each->usePublishedPage();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'html' => view('frontend.service_providers.partials.premium-cards', [
                    'premiumServiceProviders' => $service_providers->getCollection(),
                    'hasLocation' => $hasLocation,
                ])->render(),
                'next_page_url' => $service_providers->nextPageUrl(),
                'loaded_to' => $service_providers->lastItem() ?? 0,
                'total' => $service_providers->total(),
            ]);
        }

        return view('frontend/service_providers/premium', [
            'service_providers' => $service_providers,
            'categories' => $categories,
            'categoriesForFilter' => $categoriesForFilter,
            'serviceProviderStats' => $this->serviceProviderListingStats(),
            'hasLocation' => $hasLocation,
            'homepageSetting' => HomepageSetting::query()->find(1),
        ]);
    }

    public function serviceProviderCategories(): View
    {
        return view('frontend/service_providers/categories', [
            'categories' => $this->serviceProviderCategoriesQuery()->get(),
            'serviceProviderStats' => $this->serviceProviderListingStats(),
        ]);
    }

    public function index(Request $request): View|JsonResponse
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->whereJsonContains('modules', 'offers')
            ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
            ->orderBy('name')
            ->get(['id', 'name']);
        $categoriesForFilter = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'children' => $category->children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'parent_id' => $child->parent_id,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $lat = $request->filled('lat') ? (float) $request->input('lat') : session('frontend_lat');
        $lng = $request->filled('lng') ? (float) $request->input('lng') : session('frontend_lng');

        $offers = $this->baseOfferQuery($request, $lat, $lng)->paginate(12)->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('frontend.offers.partials.cards', ['offers' => $offers])->render(),
                'next_page_url' => $offers->nextPageUrl(),
                'loaded_to' => $offers->lastItem() ?? 0,
                'total' => $offers->total(),
            ]);
        }

        $homepageSetting = HomepageSetting::query()->find(1);

        return view('frontend.offers.index', [
            'offers' => $offers,
            'categories' => $categories,
            'categoriesForFilter' => $categoriesForFilter,
            'homepageSetting' => $homepageSetting,
        ]);
    }

    public function show(Offer $offer): View
    {
        abort_unless($this->isPublished($offer), 404);

        return view('frontend.offers.show', [
            'offer' => $offer,
        ]);
    }

    private function baseOfferQuery(?Request $request = null, ?float $lat = null, ?float $lng = null): Builder
    {
        $request = $request ?? request();

        $query = Offer::query()
            ->where('status', 'active')
            ->when($request->filled('search'), fn (Builder $query) => $query->where('title', 'like', '%'.$request->string('search')->toString().'%'))
            ->when($request->filled('category_id'), fn (Builder $query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('subcategory_id'), fn (Builder $query) => $query->where('subcategory_id', $request->integer('subcategory_id')))
            ->when($request->filled('validity'), function (Builder $query) use ($request): void {
                $this->applyValidityFilter($query, $request->string('validity')->toString(), Carbon::today());
            }, function (Builder $query): void {
                $this->applyValidityFilter($query, 'valid', Carbon::today());
            });

        if ($lat !== null && $lng !== null) {
            return $query
                ->select('offers.*')
                ->selectRaw('CASE WHEN location_lat IS NOT NULL AND location_lng IS NOT NULL THEN (6371 * acos(cos(radians(?)) * cos(radians(location_lat)) * cos(radians(location_lng) - radians(?)) + sin(radians(?)) * sin(radians(location_lat)))) ELSE NULL END as distance_km', [$lat, $lng, $lat])
                ->orderByRaw('CASE WHEN distance_km IS NULL THEN 1 ELSE 0 END')
                ->orderBy('distance_km')
                ->latest('id');
        }

        return $query->latest('id');
    }

    private function applyValidityFilter(Builder $query, string $validity, Carbon $today): void
    {
        match ($validity) {
            'expired' => $query->whereDate('valid_until', '<', $today),
            'expires_today' => $query->whereDate('valid_until', '=', $today),
            'no_expiry' => $query->whereNull('valid_until'),
            default => $query->where(function (Builder $validityQuery) use ($today): void {
                $validityQuery->whereNull('valid_until')->orWhereDate('valid_until', '>=', $today);
            }),
        };
    }

    private function isPublished(Offer $offer): bool
    {
        if ($offer->status !== 'active') {
            return false;
        }

        return $offer->valid_until === null || $offer->valid_until->isToday() || $offer->valid_until->isFuture();
    }

    /**
     * @return array{
     *     lat: float|null,
     *     lng: float|null,
     *     hasLocation: bool,
     *     categories: \Illuminate\Database\Eloquent\Collection,
     *     categoriesForFilter: array<int, array<string, mixed>>,
     *     vendors: \Illuminate\Contracts\Pagination\LengthAwarePaginator,
     *     cardView: string
     * }
     */
    private function vendorListingPageData(Request $request, int $perPage): array
    {
        $lat = $request->filled('lat') ? (float) $request->input('lat') : session('frontend_lat');
        $lng = $request->filled('lng') ? (float) $request->input('lng') : session('frontend_lng');
        $hasLocation = is_numeric($lat) && is_numeric($lng);

        $categories = Category::query()
            ->whereNull('parent_id')
            ->forModule('vendors')
            ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
            ->orderBy('name')
            ->get(['id', 'name']);

        $categoriesForFilter = $this->mapCategoriesForFilter($categories);

        $vendors = $this->topVendorsQuery($lat, $lng, $request)
            ->paginate($perPage)
            ->appends($request->query());
        $vendors->getCollection()->each->usePublishedPage();

        $cardView = $request->string('view')->toString() === 'list' ? 'list' : 'grid';

        return compact('lat', 'lng', 'hasLocation', 'categories', 'categoriesForFilter', 'vendors', 'cardView');
    }

    /**
     * @param  array{
     *     lat: float|null,
     *     lng: float|null,
     *     hasLocation: bool,
     *     vendors: \Illuminate\Contracts\Pagination\LengthAwarePaginator,
     *     cardView: string
     * }  $listingData
     */
    private function vendorListingJsonResponse(Request $request, array $listingData, bool $includePremiumSection): JsonResponse
    {
        $cardsPartial = $listingData['cardView'] === 'list'
            ? 'frontend.vendors.partials.list-cards'
            : 'frontend.vendors.partials.cards';

        $payload = [
            'html' => view($cardsPartial, [
                'vendors' => $listingData['vendors'],
                'hasLocation' => $listingData['hasLocation'],
            ])->render(),
            'next_page_url' => $listingData['vendors']->nextPageUrl(),
            'loaded_to' => $listingData['vendors']->lastItem() ?? 0,
            'total' => $listingData['vendors']->total(),
        ];

        if ($includePremiumSection) {
            $premiumVendors = $this->topVendorsQuery($listingData['lat'], $listingData['lng'], $request)
                ->where('is_premium', true)
                ->limit(5)
                ->get();
            $premiumVendors->each->usePublishedPage();

            $payload['premium_html'] = view('frontend.vendors.partials.premium-cards', [
                'premiumVendors' => $premiumVendors,
                'hasLocation' => $listingData['hasLocation'],
            ])->render();
            $payload['premium_total'] = $premiumVendors->count();
        }

        return response()->json($payload);
    }

    private function topVendorsQuery(?float $lat, ?float $lng, Request|string|null $requestOrSearch = null): Builder
    {
        $request = $requestOrSearch instanceof Request ? $requestOrSearch : null;
        $search = $request
            ? $request->string('search')->trim()->toString()
            : trim((string) ($requestOrSearch ?? ''));

        $query = Vendor::query()
            ->where('status', 'approved')
            ->publiclyVisible()
            ->with([
                'products' => fn ($productQuery) => $productQuery
                    ->where('status', 'approved')
                    ->select(['id', 'vendor_id', 'name', 'images', 'latitude', 'longitude', 'category_id', 'subcategory_id', 'is_online_sale'])
                    ->with(['category:id,name', 'subcategory:id,name']),
                'branches:id,vendor_id,address,city,state,is_primary',
                'bannerSlides:id,vendor_id,image_path,sort_order',
            ])
            ->withCount([
                'products' => fn (Builder $productQuery) => $productQuery->where('status', 'approved'),
                'inquiries',
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('company_name', 'like', '%'.$search.'%')
                        ->orWhere('display_name', 'like', '%'.$search.'%')
                        ->orWhereHas('products', fn (Builder $productQuery) => $productQuery
                            ->where('status', 'approved')
                            ->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->when($request?->filled('category_id'), fn (Builder $query) => $query->whereHas(
                'products',
                fn (Builder $productQuery) => $productQuery
                    ->where('status', 'approved')
                    ->where('category_id', $request->integer('category_id'))
            ))
            ->when($request?->filled('subcategory_id'), fn (Builder $query) => $query->whereHas(
                'products',
                fn (Builder $productQuery) => $productQuery
                    ->where('status', 'approved')
                    ->where('subcategory_id', $request->integer('subcategory_id'))
            ))
            ->when($request?->boolean('premium'), fn (Builder $query) => $query->where('is_premium', true))
            ->when($request?->boolean('verified'), fn (Builder $query) => $query->publiclyVisible())
            ->when($request?->filled('payment'), function (Builder $query) use ($request): void {
                if ($request->input('payment') === 'online') {
                    $query->whereHas('products', fn (Builder $productQuery) => $productQuery
                        ->where('status', 'approved')
                        ->where('is_online_sale', true));
                } elseif ($request->input('payment') === 'offline') {
                    $query->whereHas('products', fn (Builder $productQuery) => $productQuery
                        ->where('status', 'approved')
                        ->where('is_online_sale', false));
                }
            })
            ->when($request?->filled('min_rating'), function (Builder $query) use ($request): void {
                $minProducts = $this->minProductsForRatingFilter((float) $request->input('min_rating'));
                $query->whereRaw(
                    '(SELECT COUNT(*) FROM vendor_products WHERE vendor_products.vendor_id = vendors.id AND vendor_products.status = ?) >= ?',
                    ['approved', $minProducts]
                );
            });

        if (is_numeric($lat) && is_numeric($lng)) {
            $query->selectRaw('(
                    SELECT MIN(6371 * acos(cos(radians(?)) * cos(radians(vendor_products.latitude)) * cos(radians(vendor_products.longitude) - radians(?)) + sin(radians(?)) * sin(radians(vendor_products.latitude))))
                    FROM vendor_products
                    WHERE vendor_products.vendor_id = vendors.id
                    AND vendor_products.status = ?
                    AND vendor_products.latitude IS NOT NULL
                    AND vendor_products.longitude IS NOT NULL
                ) as nearest_distance_km', [$lat, $lng, $lat, 'approved']);

            if ($request?->filled('radius')) {
                $radius = max(1, (float) $request->input('radius'));
                $query->whereExists(function ($subQuery) use ($lat, $lng, $radius): void {
                    $subQuery->selectRaw('1')
                        ->from('vendor_products')
                        ->whereColumn('vendor_products.vendor_id', 'vendors.id')
                        ->where('vendor_products.status', 'approved')
                        ->whereNotNull('vendor_products.latitude')
                        ->whereNotNull('vendor_products.longitude')
                        ->whereRaw(
                            '(6371 * acos(cos(radians(?)) * cos(radians(vendor_products.latitude)) * cos(radians(vendor_products.longitude) - radians(?)) + sin(radians(?)) * sin(radians(vendor_products.latitude)))) <= ?',
                            [$lat, $lng, $lat, $radius]
                        );
                });
            }

            $this->applyVendorListingOrder($query, $request, true);
        } else {
            $this->applyVendorListingOrder($query, $request, false);
        }

        return $query;
    }

    private function applyVendorListingOrder(Builder $query, ?Request $request, bool $hasLocation): void
    {
        $tab = $request?->string('tab')->toString() ?: 'all';
        $sort = $request?->string('sort')->toString() ?: 'recent';

        if ($tab === 'recent') {
            $query->orderByDesc('created_at')->orderByDesc('id');
        } elseif ($tab === 'top_rated') {
            $productsCountSql = $this->approvedVendorProductsCountSql();
            $query->orderByDesc('is_premium')
                ->orderByRaw('(4 + LEAST(COALESCE('.$productsCountSql.', 0), 40) * 0.025) DESC')
                ->orderByRaw($productsCountSql.' DESC')
                ->orderByDesc('id');
        } elseif ($tab === 'most_reviewed') {
            $productsCountSql = $this->approvedVendorProductsCountSql();
            $inquiriesCountSql = $this->vendorInquiriesCountSql();
            $query->orderByRaw($inquiriesCountSql.' DESC')
                ->orderByRaw($productsCountSql.' DESC')
                ->orderByDesc('created_at')
                ->orderByDesc('id');
        } elseif ($sort === 'name') {
            $query->orderByRaw('COALESCE(display_name, company_name) asc');
        } elseif ($hasLocation) {
            $query->orderByRaw('CASE WHEN nearest_distance_km IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('is_premium')
                ->orderBy('nearest_distance_km')
                ->latest('id');
        } else {
            $query->orderByDesc('is_premium')->latest('created_at')->latest('id');
        }
    }

    private function minProductsForRatingFilter(float $rating): int
    {
        return match (true) {
            $rating >= 4.5 => 10,
            $rating >= 4.0 => 5,
            $rating >= 3.5 => 3,
            $rating >= 3.0 => 2,
            default => 1,
        };
    }

    private function approvedVendorProductsCountSql(): string
    {
        return '(SELECT COUNT(*) FROM vendor_products WHERE vendor_products.vendor_id = vendors.id AND vendor_products.status = \'approved\')';
    }

    private function vendorInquiriesCountSql(): string
    {
        return '(SELECT COUNT(*) FROM vendor_product_inquiries WHERE vendor_product_inquiries.vendor_id = vendors.id)';
    }

    /**
     * @return array{premium:int,trusted:int,categories:int,happy_customers:int}
     */
    private function vendorListingStats(): array
    {
        $baseVendorQuery = Vendor::query()->where('status', 'approved')->publiclyVisible();

        return [
            'premium' => (clone $baseVendorQuery)->where('is_premium', true)->count(),
            'trusted' => (clone $baseVendorQuery)->count(),
            'categories' => Category::query()->whereNull('parent_id')->forModule('vendors')->count(),
            'happy_customers' => User::query()->count(),
        ];
    }

    /**
     * @return Collection<int, object{id:int,name:string,vendor_count:int}>
     */
    private function topVendorCategories(int $limit = 16): Collection
    {
        return $this->vendorCategoriesQuery()
            ->limit($limit)
            ->get()
            ->values();
    }

    public function vendorCategories(): View
    {
        return view('frontend/vendors/categories', [
            'categories' => $this->vendorCategoriesQuery()->get(),
            'vendorStats' => $this->vendorListingStats(),
        ]);
    }

    private function vendorCategoriesQuery(): Builder
    {
        return Category::query()
            ->whereNull('parent_id')
            ->forModule('vendors')
            ->select(['categories.id', 'categories.name'])
            ->selectSub(function ($query): void {
                $query->from('vendor_products')
                    ->join('vendors', 'vendors.id', '=', 'vendor_products.vendor_id')
                    ->whereColumn('vendor_products.category_id', 'categories.id')
                    ->where('vendor_products.status', 'approved')
                    ->where('vendors.status', 'approved')
                    ->where(function ($vendorQuery): void {
                        $vendorQuery->whereNotNull('vendors.published_page_data')
                            ->orWhere('vendors.public_page_status', 'approved');
                    })
                    ->selectRaw('COUNT(DISTINCT vendor_products.vendor_id)');
            }, 'vendor_count')
            ->orderByDesc('vendor_count')
            ->orderBy('name');
    }

    private function topConsultantsQuery(?float $lat, ?float $lng, Request|string|null $requestOrSearch = null): Builder
    {
        $request = $requestOrSearch instanceof Request ? $requestOrSearch : null;
        $search = $request
            ? $request->string('search')->trim()->toString()
            : trim((string) ($requestOrSearch ?? ''));

        $query = Consultant::query()
            ->where('status', 'approved')
            ->publiclyVisible()
            ->with([
                'services' => fn ($serviceQuery) => $serviceQuery
                    ->where('status', 'approved')
                    ->select(['id', 'consultant_id', 'name', 'image_path', 'latitude', 'longitude', 'category_id', 'subcategory_id', 'is_online'])
                    ->with(['categoryModel:id,name', 'subcategoryModel:id,name']),
                'branches:id,consultant_id,address,city,state,logo,is_primary',
                'bannerSlides:id,consultant_id,image_path,sort_order',
            ])
            ->withCount(['services' => fn (Builder $serviceQuery) => $serviceQuery->where('status', 'approved')])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('company_name', 'like', '%'.$search.'%')
                        ->orWhere('display_name', 'like', '%'.$search.'%')
                        ->orWhereHas('services', fn (Builder $serviceQuery) => $serviceQuery
                            ->where('status', 'approved')
                            ->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->when($request?->filled('category_id'), fn (Builder $query) => $query->whereHas(
                'services',
                fn (Builder $serviceQuery) => $serviceQuery
                    ->where('status', 'approved')
                    ->where('category_id', $request->integer('category_id'))
            ))
            ->when($request?->filled('subcategory_id'), fn (Builder $query) => $query->whereHas(
                'services',
                fn (Builder $serviceQuery) => $serviceQuery
                    ->where('status', 'approved')
                    ->where('subcategory_id', $request->integer('subcategory_id'))
            ))
            ->when($request?->boolean('premium'), fn (Builder $query) => $query->where('is_premium', true))
            ->when($request?->boolean('verified'), fn (Builder $query) => $query->publiclyVisible())
            ->when($request?->filled('payment'), function (Builder $query) use ($request): void {
                if ($request->input('payment') === 'online') {
                    $query->whereHas('services', fn (Builder $serviceQuery) => $serviceQuery
                        ->where('status', 'approved')
                        ->where('is_online', true));
                } elseif ($request->input('payment') === 'offline') {
                    $query->whereHas('services', fn (Builder $serviceQuery) => $serviceQuery
                        ->where('status', 'approved')
                        ->where('is_online', false));
                }
            })
            ->when($request?->filled('min_rating'), function (Builder $query) use ($request): void {
                $minServices = $this->minProductsForRatingFilter((float) $request->input('min_rating'));
                $query->whereRaw(
                    '(SELECT COUNT(*) FROM consultant_services WHERE consultant_services.consultant_id = consultants.id AND consultant_services.status = ?) >= ?',
                    ['approved', $minServices]
                );
            });

        if (is_numeric($lat) && is_numeric($lng)) {
            $query->selectRaw('(
                    SELECT MIN(6371 * acos(cos(radians(?)) * cos(radians(consultant_services.latitude)) * cos(radians(consultant_services.longitude) - radians(?)) + sin(radians(?)) * sin(radians(consultant_services.latitude))))
                    FROM consultant_services
                    WHERE consultant_services.consultant_id = consultants.id
                    AND consultant_services.status = ?
                    AND consultant_services.latitude IS NOT NULL
                    AND consultant_services.longitude IS NOT NULL
                ) as nearest_distance_km', [$lat, $lng, $lat, 'approved']);

            if ($request?->filled('radius')) {
                $radius = max(1, (float) $request->input('radius'));
                $query->whereExists(function ($subQuery) use ($lat, $lng, $radius): void {
                    $subQuery->selectRaw('1')
                        ->from('consultant_services')
                        ->whereColumn('consultant_services.consultant_id', 'consultants.id')
                        ->where('consultant_services.status', 'approved')
                        ->whereNotNull('consultant_services.latitude')
                        ->whereNotNull('consultant_services.longitude')
                        ->whereRaw(
                            '(6371 * acos(cos(radians(?)) * cos(radians(consultant_services.latitude)) * cos(radians(consultant_services.longitude) - radians(?)) + sin(radians(?)) * sin(radians(consultant_services.latitude)))) <= ?',
                            [$lat, $lng, $lat, $radius]
                        );
                });
            }

            $this->applyConsultantListingOrder($query, $request, true);
        } else {
            $this->applyConsultantListingOrder($query, $request, false);
        }

        return $query;
    }

    private function topServiceProvidersQuery(?float $lat, ?float $lng, Request|string|null $requestOrSearch = null): Builder
    {
        $request = $requestOrSearch instanceof Request ? $requestOrSearch : null;
        $search = $request
            ? $request->string('search')->trim()->toString()
            : trim((string) ($requestOrSearch ?? ''));

        $query = ServiceProvider::query()
            ->where('status', 'approved')
            ->publiclyVisible()
            ->with([
                'services' => fn ($serviceQuery) => $serviceQuery
                    ->where('status', 'approved')
                    ->select(['id', 'service_provider_id', 'name', 'image_path', 'latitude', 'longitude', 'category_id', 'subcategory_id', 'is_online'])
                    ->with(['categoryModel:id,name', 'subcategoryModel:id,name']),
                'branches:id,service_provider_id,address,city,state,logo,is_primary',
                'bannerSlides:id,service_provider_id,image_path,sort_order',
            ])
            ->withCount(['services' => fn (Builder $serviceQuery) => $serviceQuery->where('status', 'approved')])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $searchQuery) use ($search): void {
                    $searchQuery
                        ->where('company_name', 'like', '%'.$search.'%')
                        ->orWhere('display_name', 'like', '%'.$search.'%')
                        ->orWhereHas('services', fn (Builder $serviceQuery) => $serviceQuery
                            ->where('status', 'approved')
                            ->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->when($request?->filled('category_id'), fn (Builder $query) => $query->whereHas(
                'services',
                fn (Builder $serviceQuery) => $serviceQuery
                    ->where('status', 'approved')
                    ->where('category_id', $request->integer('category_id'))
            ))
            ->when($request?->filled('subcategory_id'), fn (Builder $query) => $query->whereHas(
                'services',
                fn (Builder $serviceQuery) => $serviceQuery
                    ->where('status', 'approved')
                    ->where('subcategory_id', $request->integer('subcategory_id'))
            ))
            ->when($request?->boolean('premium'), fn (Builder $query) => $query->where('is_premium', true))
            ->when($request?->boolean('verified'), fn (Builder $query) => $query->publiclyVisible())
            ->when($request?->filled('payment'), function (Builder $query) use ($request): void {
                if ($request->input('payment') === 'online') {
                    $query->whereHas('services', fn (Builder $serviceQuery) => $serviceQuery
                        ->where('status', 'approved')
                        ->where('is_online', true));
                } elseif ($request->input('payment') === 'offline') {
                    $query->whereHas('services', fn (Builder $serviceQuery) => $serviceQuery
                        ->where('status', 'approved')
                        ->where('is_online', false));
                }
            })
            ->when($request?->filled('min_rating'), function (Builder $query) use ($request): void {
                $minServices = $this->minProductsForRatingFilter((float) $request->input('min_rating'));
                $query->whereRaw(
                    '(SELECT COUNT(*) FROM service_provider_services WHERE service_provider_services.service_provider_id = service_providers.id AND service_provider_services.status = ?) >= ?',
                    ['approved', $minServices]
                );
            });

        if (is_numeric($lat) && is_numeric($lng)) {
            $query->selectRaw('(
                    SELECT MIN(6371 * acos(cos(radians(?)) * cos(radians(service_provider_services.latitude)) * cos(radians(service_provider_services.longitude) - radians(?)) + sin(radians(?)) * sin(radians(service_provider_services.latitude))))
                    FROM service_provider_services
                    WHERE service_provider_services.service_provider_id = service_providers.id
                    AND service_provider_services.status = ?
                    AND service_provider_services.latitude IS NOT NULL
                    AND service_provider_services.longitude IS NOT NULL
                ) as nearest_distance_km', [$lat, $lng, $lat, 'approved']);

            if ($request?->filled('radius')) {
                $radius = max(1, (float) $request->input('radius'));
                $query->whereExists(function ($subQuery) use ($lat, $lng, $radius): void {
                    $subQuery->selectRaw('1')
                        ->from('service_provider_services')
                        ->whereColumn('service_provider_services.service_provider_id', 'service_providers.id')
                        ->where('service_provider_services.status', 'approved')
                        ->whereNotNull('service_provider_services.latitude')
                        ->whereNotNull('service_provider_services.longitude')
                        ->whereRaw(
                            '(6371 * acos(cos(radians(?)) * cos(radians(service_provider_services.latitude)) * cos(radians(service_provider_services.longitude) - radians(?)) + sin(radians(?)) * sin(radians(service_provider_services.latitude)))) <= ?',
                            [$lat, $lng, $lat, $radius]
                        );
                });
            }

            $this->applyServiceProviderListingOrder($query, $request, true);
        } else {
            $this->applyServiceProviderListingOrder($query, $request, false);
        }

        return $query;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Collection<int, Category>  $categories
     * @return array<int, array<string, mixed>>
     */
    private function mapCategoriesForFilter(Collection $categories): array
    {
        return $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'children' => $category->children->map(function ($child) {
                    return [
                        'id' => $child->id,
                        'name' => $child->name,
                        'parent_id' => $child->parent_id,
                    ];
                })->values()->all(),
            ];
        })->values()->all();
    }

    /**
     * @return array{
     *     lat: float|null,
     *     lng: float|null,
     *     hasLocation: bool,
     *     categories: \Illuminate\Database\Eloquent\Collection,
     *     categoriesForFilter: array<int, array<string, mixed>>,
     *     consultants: \Illuminate\Contracts\Pagination\LengthAwarePaginator,
     *     cardView: string
     * }
     */
    private function consultantListingPageData(Request $request, int $perPage): array
    {
        $lat = $request->filled('lat') ? (float) $request->input('lat') : session('frontend_lat');
        $lng = $request->filled('lng') ? (float) $request->input('lng') : session('frontend_lng');
        $hasLocation = is_numeric($lat) && is_numeric($lng);

        $categories = Category::query()
            ->whereNull('parent_id')
            ->forModule('consultants')
            ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
            ->orderBy('name')
            ->get(['id', 'name']);

        $consultants = $this->topConsultantsQuery($lat, $lng, $request)
            ->paginate($perPage)
            ->appends($request->query());
        $consultants->getCollection()->each->usePublishedPage();

        return [
            'lat' => $lat,
            'lng' => $lng,
            'hasLocation' => $hasLocation,
            'categories' => $categories,
            'categoriesForFilter' => $this->mapCategoriesForFilter($categories),
            'consultants' => $consultants,
            'cardView' => $request->string('view')->toString() === 'list' ? 'list' : 'grid',
        ];
    }

    /**
     * @param  array{
     *     lat: float|null,
     *     lng: float|null,
     *     hasLocation: bool,
     *     consultants: \Illuminate\Contracts\Pagination\LengthAwarePaginator,
     *     cardView: string
     * }  $listingData
     */
    private function consultantListingJsonResponse(Request $request, array $listingData, bool $includePremiumSection): JsonResponse
    {
        $cardsPartial = $listingData['cardView'] === 'list'
            ? 'frontend.consultants.partials.list-cards'
            : 'frontend.consultants.partials.cards';

        $payload = [
            'html' => view($cardsPartial, [
                'consultants' => $listingData['consultants'],
                'hasLocation' => $listingData['hasLocation'],
            ])->render(),
            'next_page_url' => $listingData['consultants']->nextPageUrl(),
            'loaded_to' => $listingData['consultants']->lastItem() ?? 0,
            'total' => $listingData['consultants']->total(),
        ];

        if ($includePremiumSection) {
            $premiumConsultants = $this->topConsultantsQuery($listingData['lat'], $listingData['lng'], $request)
                ->where('is_premium', true)
                ->limit(5)
                ->get();
            $premiumConsultants->each->usePublishedPage();

            $payload['premium_html'] = view('frontend.consultants.partials.premium-cards', [
                'premiumConsultants' => $premiumConsultants,
                'hasLocation' => $listingData['hasLocation'],
            ])->render();
            $payload['premium_total'] = $premiumConsultants->count();
        }

        return response()->json($payload);
    }

    /**
     * @return array{
     *     lat: float|null,
     *     lng: float|null,
     *     hasLocation: bool,
     *     categories: \Illuminate\Database\Eloquent\Collection,
     *     categoriesForFilter: array<int, array<string, mixed>>,
     *     service_providers: \Illuminate\Contracts\Pagination\LengthAwarePaginator,
     *     cardView: string
     * }
     */
    private function serviceProviderListingPageData(Request $request, int $perPage): array
    {
        $lat = $request->filled('lat') ? (float) $request->input('lat') : session('frontend_lat');
        $lng = $request->filled('lng') ? (float) $request->input('lng') : session('frontend_lng');
        $hasLocation = is_numeric($lat) && is_numeric($lng);

        $categories = Category::query()
            ->whereNull('parent_id')
            ->forModule('service_providers')
            ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
            ->orderBy('name')
            ->get(['id', 'name']);

        $service_providers = $this->topServiceProvidersQuery($lat, $lng, $request)
            ->paginate($perPage)
            ->appends($request->query());
        $service_providers->getCollection()->each->usePublishedPage();

        return [
            'lat' => $lat,
            'lng' => $lng,
            'hasLocation' => $hasLocation,
            'categories' => $categories,
            'categoriesForFilter' => $this->mapCategoriesForFilter($categories),
            'service_providers' => $service_providers,
            'cardView' => $request->string('view')->toString() === 'list' ? 'list' : 'grid',
        ];
    }

    /**
     * @param  array{
     *     lat: float|null,
     *     lng: float|null,
     *     hasLocation: bool,
     *     service_providers: \Illuminate\Contracts\Pagination\LengthAwarePaginator,
     *     cardView: string
     * }  $listingData
     */
    private function serviceProviderListingJsonResponse(Request $request, array $listingData, bool $includePremiumSection): JsonResponse
    {
        $cardsPartial = $listingData['cardView'] === 'list'
            ? 'frontend.service_providers.partials.list-cards'
            : 'frontend.service_providers.partials.cards';

        $payload = [
            'html' => view($cardsPartial, [
                'service_providers' => $listingData['service_providers'],
                'hasLocation' => $listingData['hasLocation'],
            ])->render(),
            'next_page_url' => $listingData['service_providers']->nextPageUrl(),
            'loaded_to' => $listingData['service_providers']->lastItem() ?? 0,
            'total' => $listingData['service_providers']->total(),
        ];

        if ($includePremiumSection) {
            $premiumServiceProviders = $this->topServiceProvidersQuery($listingData['lat'], $listingData['lng'], $request)
                ->where('is_premium', true)
                ->limit(5)
                ->get();
            $premiumServiceProviders->each->usePublishedPage();

            $payload['premium_html'] = view('frontend.service_providers.partials.premium-cards', [
                'premiumServiceProviders' => $premiumServiceProviders,
                'hasLocation' => $listingData['hasLocation'],
            ])->render();
            $payload['premium_total'] = $premiumServiceProviders->count();
        }

        return response()->json($payload);
    }

    private function applyConsultantListingOrder(Builder $query, ?Request $request, bool $hasLocation): void
    {
        $tab = $request?->string('tab')->toString() ?: 'all';
        $sort = $request?->string('sort')->toString() ?: 'recent';
        $servicesCountSql = $this->approvedConsultantServicesCountSql();

        if ($tab === 'recent') {
            $query->orderByDesc('created_at')->orderByDesc('id');
        } elseif ($tab === 'top_rated') {
            $query->orderByDesc('is_premium')
                ->orderByRaw('(4 + LEAST(COALESCE('.$servicesCountSql.', 0), 40) * 0.025) DESC')
                ->orderByRaw($servicesCountSql.' DESC')
                ->orderByDesc('id');
        } elseif ($tab === 'most_reviewed') {
            $query->orderByRaw($servicesCountSql.' DESC')
                ->orderByDesc('created_at')
                ->orderByDesc('id');
        } elseif ($sort === 'name') {
            $query->orderByRaw('COALESCE(display_name, company_name) asc');
        } elseif ($hasLocation) {
            $query->orderByRaw('CASE WHEN nearest_distance_km IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('is_premium')
                ->orderBy('nearest_distance_km')
                ->latest('id');
        } else {
            $query->orderByDesc('is_premium')->latest('created_at')->latest('id');
        }
    }

    private function applyServiceProviderListingOrder(Builder $query, ?Request $request, bool $hasLocation): void
    {
        $tab = $request?->string('tab')->toString() ?: 'all';
        $sort = $request?->string('sort')->toString() ?: 'recent';
        $servicesCountSql = $this->approvedServiceProviderServicesCountSql();

        if ($tab === 'recent') {
            $query->orderByDesc('created_at')->orderByDesc('id');
        } elseif ($tab === 'top_rated') {
            $query->orderByDesc('is_premium')
                ->orderByRaw('(4 + LEAST(COALESCE('.$servicesCountSql.', 0), 40) * 0.025) DESC')
                ->orderByRaw($servicesCountSql.' DESC')
                ->orderByDesc('id');
        } elseif ($tab === 'most_reviewed') {
            $query->orderByRaw($servicesCountSql.' DESC')
                ->orderByDesc('created_at')
                ->orderByDesc('id');
        } elseif ($sort === 'name') {
            $query->orderByRaw('COALESCE(display_name, company_name) asc');
        } elseif ($hasLocation) {
            $query->orderByRaw('CASE WHEN nearest_distance_km IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('is_premium')
                ->orderBy('nearest_distance_km')
                ->latest('id');
        } else {
            $query->orderByDesc('is_premium')->latest('created_at')->latest('id');
        }
    }

    private function approvedConsultantServicesCountSql(): string
    {
        return '(SELECT COUNT(*) FROM consultant_services WHERE consultant_services.consultant_id = consultants.id AND consultant_services.status = \'approved\')';
    }

    private function approvedServiceProviderServicesCountSql(): string
    {
        return '(SELECT COUNT(*) FROM service_provider_services WHERE service_provider_services.service_provider_id = service_providers.id AND service_provider_services.status = \'approved\')';
    }

    /**
     * @return array{premium:int,trusted:int,categories:int,happy_customers:int}
     */
    private function consultantListingStats(): array
    {
        $baseQuery = Consultant::query()->where('status', 'approved')->publiclyVisible();

        return [
            'premium' => (clone $baseQuery)->where('is_premium', true)->count(),
            'trusted' => (clone $baseQuery)->count(),
            'categories' => Category::query()->whereNull('parent_id')->forModule('consultants')->count(),
            'happy_customers' => User::query()->count(),
        ];
    }

    /**
     * @return array{premium:int,trusted:int,categories:int,happy_customers:int}
     */
    private function serviceProviderListingStats(): array
    {
        $baseQuery = ServiceProvider::query()->where('status', 'approved')->publiclyVisible();

        return [
            'premium' => (clone $baseQuery)->where('is_premium', true)->count(),
            'trusted' => (clone $baseQuery)->count(),
            'categories' => Category::query()->whereNull('parent_id')->forModule('service_providers')->count(),
            'happy_customers' => User::query()->count(),
        ];
    }

    /**
     * @return Collection<int, object{id:int,name:string,consultant_count:int}>
     */
    private function topConsultantCategories(int $limit = 16): Collection
    {
        return $this->consultantCategoriesQuery()
            ->limit($limit)
            ->get()
            ->values();
    }

    /**
     * @return Collection<int, object{id:int,name:string,service_provider_count:int}>
     */
    private function topServiceProviderCategories(int $limit = 16): Collection
    {
        return $this->serviceProviderCategoriesQuery()
            ->limit($limit)
            ->get()
            ->values();
    }

    private function consultantCategoriesQuery(): Builder
    {
        return Category::query()
            ->whereNull('parent_id')
            ->forModule('consultants')
            ->select(['categories.id', 'categories.name'])
            ->selectSub(function ($query): void {
                $query->from('consultant_services')
                    ->join('consultants', 'consultants.id', '=', 'consultant_services.consultant_id')
                    ->whereColumn('consultant_services.category_id', 'categories.id')
                    ->where('consultant_services.status', 'approved')
                    ->where('consultants.status', 'approved')
                    ->selectRaw('COUNT(DISTINCT consultant_services.consultant_id)');
            }, 'consultant_count')
            ->orderByDesc('consultant_count')
            ->orderBy('name');
    }

    private function serviceProviderCategoriesQuery(): Builder
    {
        return Category::query()
            ->whereNull('parent_id')
            ->forModule('service_providers')
            ->select(['categories.id', 'categories.name'])
            ->selectSub(function ($query): void {
                $query->from('service_provider_services')
                    ->join('service_providers', 'service_providers.id', '=', 'service_provider_services.service_provider_id')
                    ->whereColumn('service_provider_services.category_id', 'categories.id')
                    ->where('service_provider_services.status', 'approved')
                    ->where('service_providers.status', 'approved')
                    ->selectRaw('COUNT(DISTINCT service_provider_services.service_provider_id)');
            }, 'service_provider_count')
            ->orderByDesc('service_provider_count')
            ->orderBy('name');
    }

    /**
     * @param  Collection<int, UserAd>  $ads
     * @return array<int, array<int, string>>
     */
    private function resolveSelectedCategoryNamesByAdId(Collection $ads): array
    {
        $ads = $ads->values();
        if ($ads->isEmpty()) {
            return [];
        }

        $selectedCategoryIds = $ads
            ->flatMap(fn (UserAd $ad) => array_map('intval', $ad->selected_category_ids ?? []))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        $categoryNamesById = Category::query()
            ->whereIn('id', $selectedCategoryIds)
            ->pluck('name', 'id');

        return $ads
            ->mapWithKeys(function (UserAd $ad) use ($categoryNamesById) {
                $selectedNames = collect($ad->selected_category_ids ?? [])
                    ->map(fn ($id) => $categoryNamesById->get((int) $id))
                    ->filter(fn ($name) => is_string($name) && $name !== '')
                    ->values()
                    ->all();

                if ($selectedNames === [] && $ad->category?->name) {
                    $selectedNames = [$ad->category->name];
                }

                return [$ad->id => $selectedNames];
            })
            ->all();
    }
}
