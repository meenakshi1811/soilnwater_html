<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomepageSetting;
use App\Models\Offer;
use App\Models\UserAd;
use App\Models\Vendor;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

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
            ->get(['id', 'title', 'category_id', 'final_image', 'created_at']);

        $homepageSetting = HomepageSetting::query()->find(1);

        return view('frontend.index', [
            'offers' => $offers,
            'topCategoriesSliderAds' => $frontPageAds->where('size_type', 'top_categories_ad_1')->values(),
            'topSidebarSliderAds' => $frontPageAds->where('size_type', 'top_categories_ad_2')->values(),
            'sponsoredListingsAds' => $frontPageAds->where('size_type', 'sponsored_listings_ad')->values(),
            'belowSponsoredSliderAds' => $frontPageAds->where('size_type', 'below_sponsored_ad')->values(),
            'ecommerceSideSliderAds' => $frontPageAds->where('size_type', 'ecommerce_ad')->values(),
            'recentApprovedAds' => $recentApprovedAds,
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
            'vendorEnquiryCategories' => Category::query()
                ->whereNull('parent_id')
                ->whereJsonContains('modules', 'enquiry')
                ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
                ->orderBy('name')
                ->get(['id', 'name']),
            'hasLocation' => is_numeric($lat) && is_numeric($lng),
            'homepageSetting' => $homepageSetting,
        ]);
    }

    public function vendors(Request $request): View
    {
        $lat = $request->filled('lat') ? (float) $request->input('lat') : session('frontend_lat');
        $lng = $request->filled('lng') ? (float) $request->input('lng') : session('frontend_lng');

        $vendors = $this->topVendorsQuery($lat, $lng)->paginate(24)->appends($request->query());

        return view('frontend/vendors/index', [
            'vendors' => $vendors,
            'hasLocation' => is_numeric($lat) && is_numeric($lng),
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

    private function topVendorsQuery(?float $lat, ?float $lng): Builder
    {
        $query = Vendor::query()
            ->where('status', 'approved')
            ->with(['products:id,vendor_id,name,images,latitude,longitude', 'branches:id,vendor_id,address,city,state,is_primary', 'bannerSlides:id,vendor_id,image_path,sort_order'])
            ->withCount('products');

        if (is_numeric($lat) && is_numeric($lng)) {
            $query->select('vendors.*')
                ->selectRaw('(
                    SELECT MIN(6371 * acos(cos(radians(?)) * cos(radians(vendor_products.latitude)) * cos(radians(vendor_products.longitude) - radians(?)) + sin(radians(?)) * sin(radians(vendor_products.latitude))))
                    FROM vendor_products
                    WHERE vendor_products.vendor_id = vendors.id
                    AND vendor_products.latitude IS NOT NULL
                    AND vendor_products.longitude IS NOT NULL
                ) as nearest_distance_km', [$lat, $lng, $lat])
                ->orderByRaw('CASE WHEN nearest_distance_km IS NULL THEN 1 ELSE 0 END')
                ->orderByDesc('is_premium')
                ->orderBy('nearest_distance_km')
                ->latest('id');
        } else {
            $query->orderByDesc('is_premium')->latest('created_at')->latest('id');
        }

        return $query;
    }
}
