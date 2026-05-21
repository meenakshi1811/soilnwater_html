<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\VendorProduct;
use App\Models\VendorProductInquiry;
use App\Models\UserAd;
use App\Services\MarketplaceAdsService;
use App\Support\AdSizes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class VendorStoreController extends Controller
{
    public function show(string $slug): View
    {
        $vendor = $this->resolveVendor($slug);

        $featuredProducts = VendorProduct::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'approved')
            ->latest('updated_at')
            ->limit(4)
            ->get();

        $vendorCategories = $this->vendorCategories($vendor);
        $adsContext = $this->loadStoreAds($vendor, $vendor->pageSections->count());

        return view('frontend.store.show', [
            'vendor' => $vendor,
            'preview' => false,
            'activeNav' => 'home',
            'featuredProducts' => $featuredProducts,
            'vendorCategories' => $vendorCategories,
            'sectionAdRails' => $adsContext['sectionAdRails'],
        ]);
    }

    public function products(string $slug): View
    {
        return $this->renderProductCatalog($slug);
    }

    public function categoryProducts(string $slug, Category $category): View
    {
        $this->assertVendorCategory($category);

        return $this->renderProductCatalog($slug, $category);
    }

    public function subcategoryProducts(string $slug, Category $category, Category $subcategory): View
    {
        $this->assertVendorCategory($category);
        abort_unless((int) $subcategory->parent_id === (int) $category->id, 404);
        $this->assertVendorCategory($subcategory, isSubcategory: true);

        return $this->renderProductCatalog($slug, $category, $subcategory);
    }

    public function productShow(string $slug, VendorProduct $product): View
    {
        $vendor = $this->resolveVendor($slug);
        abort_unless($product->vendor_id === $vendor->id && $product->status === 'approved', 404);

        $similarProducts = VendorProduct::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'approved')
            ->where('id', '!=', $product->id)
            ->when($product->subcategory_id, fn ($query) => $query->where('subcategory_id', $product->subcategory_id), function ($query) use ($product) {
                if ($product->category_id) {
                    $query->where('category_id', $product->category_id);
                }
            })
            ->latest('updated_at')
            ->limit(4)
            ->get();

        if ($vendor->is_premium) {
            return view('frontend.store.product-show', [
                'vendor' => $vendor,
                'product' => $product,
                'similarProducts' => $similarProducts,
                'vendorCategories' => $this->vendorCategories($vendor),
                'activeNav' => 'products',
                'topGroups' => collect(),
                'sideGroups' => collect(),
                'bottomGroups' => collect(),
                'ads' => collect(),
            ]);
        }

        $lat = session('frontend_lat');
        $lng = session('frontend_lng');

        $adsQuery = UserAd::query()
            ->where('status', 'approved')
            ->whereNotNull('final_image')
            ->whereDoesntHave('adSize', fn ($query) => $query->where('admin_only', true))
            ->where(function ($q) {
                $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereJsonContains('selected_modules', 'vendors')->orWhereJsonContains('selected_modules', 'products');
            });

        if (is_numeric($lat) && is_numeric($lng)) {
            $adsQuery
                ->select('user_ads.*')
                ->selectRaw('CASE WHEN location_lat IS NOT NULL AND location_lng IS NOT NULL THEN (6371 * acos(cos(radians(?)) * cos(radians(location_lat)) * cos(radians(location_lng) - radians(?)) + sin(radians(?)) * sin(radians(location_lat)))) ELSE NULL END as distance_km', [(float) $lat, (float) $lng, (float) $lat])
                ->orderByRaw('CASE WHEN distance_km IS NULL THEN 1 ELSE 0 END')
                ->orderBy('distance_km')
                ->orderByDesc('updated_at');
        } else {
            $adsQuery->orderByDesc('updated_at');
        }

        $ads = $adsQuery->with('adSize:id,size_key,width,height')->take(18)->get();

        if ($ads->isEmpty()) {
            $fallbackQuery = UserAd::query()
                ->where('status', 'approved')
                ->whereNotNull('final_image')
                ->whereDoesntHave('adSize', fn ($query) => $query->where('admin_only', true))
                ->where(function ($q) {
                    $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()->toDateString());
                })
                ->orderByDesc('updated_at');

            $ads = $fallbackQuery->with('adSize:id,size_key,width,height')->take(18)->get();
        }

        $ads = $ads->shuffle()->values();

        $sizeMap = collect(AdSizes::all())->mapWithKeys(function (array $size, string $sizeKey) {
            return [strtolower((string) $sizeKey) => ['w' => (int) ($size['w'] ?? 0), 'h' => (int) ($size['h'] ?? 0)]];
        });

        $adsByDimension = $ads
            ->filter(function ($ad) use ($sizeMap) {
                $key = strtolower((string) $ad->size_type);

                return isset($sizeMap[$key]) && $sizeMap[$key]['w'] > 0 && $sizeMap[$key]['h'] > 0;
            })
            ->groupBy(function ($ad) use ($sizeMap) {
                $key = strtolower((string) $ad->size_type);

                return $sizeMap[$key]['w'].'x'.$sizeMap[$key]['h'];
            });

        if ($adsByDimension->isEmpty() && $ads->isNotEmpty()) {
            $adsByDimension = $ads->chunk(3)->values();
        }

        $isFullPageSize = function ($ad): bool {
            $sizeType = strtolower(trim((string) ($ad->size_type ?? '')));
            $sizeKey = strtolower(trim((string) ($ad->adSize->size_key ?? '')));

            if ($sizeKey !== '') {
                return $sizeKey === 'full_page';
            }

            return $sizeType === 'full_page';
        };

        $fullPageGroups = $adsByDimension->filter(function ($group) use ($isFullPageSize) {
            $firstAd = $group->first();

            return $firstAd && $isFullPageSize($firstAd);
        })->values();

        $sideGroups = $adsByDimension->reject(function ($group) use ($isFullPageSize) {
            $firstAd = $group->first();

            return $firstAd && $isFullPageSize($firstAd);
        })->values();

        if ($fullPageGroups->isEmpty()) {
            $fullPageGroups = $ads->filter(fn ($ad) => $isFullPageSize($ad))->chunk(1)->values();
        }

        if ($sideGroups->isEmpty()) {
            $sideGroups = $ads->filter(fn ($ad) => ! $isFullPageSize($ad))->chunk(2)->values();
        }

        $topGroups = $fullPageGroups->take(1)->values();
        $bottomGroups = collect();

        return view('frontend.store.product-show', [
            'vendor' => $vendor,
            'product' => $product,
            'similarProducts' => $similarProducts,
            'vendorCategories' => $this->vendorCategories($vendor),
            'activeNav' => 'products',
            'topGroups' => $topGroups,
            'sideGroups' => $sideGroups,
            'bottomGroups' => $bottomGroups,
            'ads' => $ads,
        ]);
    }

    public function sendInquiry(Request $request, string $slug, VendorProduct $product): JsonResponse
    {
        $vendor = $this->resolveVendor($slug);
        abort_unless($product->vendor_id === $vendor->id && $product->status === 'approved', 404);

        if (! $request->user()) {
            return response()->json(['message' => 'Please login to send an enquiry.'], 403);
        }

        $data = $request->validate([
            'email' => ['required', 'email'],
            'phone_number' => ['required', 'string', 'max:20'],
            'preferred_contact' => ['required', 'in:text,whatsapp,call,email'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $inquiry = VendorProductInquiry::query()->create([
            'vendor_id' => $vendor->id,
            'vendor_product_id' => $product->id,
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        if ($vendor->email) {
            $body = view('emails.vendor.new-inquiry', compact('inquiry', 'vendor', 'product'))->render();
            Mail::send([], [], function ($message) use ($vendor, $product, $body) {
                $message->to($vendor->email)->subject('New product inquiry: '.$product->name)->html($body);
            });
        }

        return response()->json(['message' => 'Enquiry sent successfully.']);
    }

    private function renderProductCatalog(string $slug, ?Category $category = null, ?Category $subcategory = null): View
    {
        $vendor = $this->resolveVendor($slug);
        $vendorCategories = $this->vendorCategories($vendor);

        $productsQuery = VendorProduct::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'approved');

        if ($subcategory) {
            $productsQuery->where('subcategory_id', $subcategory->id);
        } elseif ($category) {
            $productsQuery->where('category_id', $category->id);
        }

        $products = $productsQuery->latest('updated_at')->paginate(12)->withQueryString();
        $adsContext = $this->loadStoreAds($vendor, 0);

        if ($subcategory) {
            $pageTitle = $subcategory->name;
            $pageSubtitle = 'Products in '.$subcategory->name.' · '.$category->name;
            $activeNav = 'subcategory';
        } elseif ($category) {
            $pageTitle = $category->name;
            $pageSubtitle = 'All products listed under '.$category->name;
            $activeNav = 'category';
        } else {
            $pageTitle = 'All products';
            $pageSubtitle = 'Browse the complete catalog from '.$vendor->publicDisplayName();
            $activeNav = 'products';
        }

        return view('frontend.store.products', [
            'vendor' => $vendor,
            'preview' => false,
            'activeNav' => $activeNav,
            'pageTitle' => $pageTitle,
            'pageSubtitle' => $pageSubtitle,
            'products' => $products,
            'vendorCategories' => $vendorCategories,
            'activeCategory' => $category,
            'activeSubcategory' => $subcategory,
            'sidebarAds' => $adsContext['sidebarAds'],
        ]);
    }

    private function resolveVendor(string $slug): Vendor
    {
        return Vendor::query()
            ->where('slug', $slug)
            ->where('status', 'approved')
            ->with(['bannerSlides', 'pageSections'])
            ->firstOrFail();
    }

    private function vendorCategories(Vendor $vendor): Collection
    {
        $categoryIds = VendorProduct::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'approved')
            ->whereNotNull('category_id')
            ->pluck('category_id')
            ->unique()
            ->filter();

        if ($categoryIds->isEmpty()) {
            return Category::query()
                ->whereNull('parent_id')
                ->whereJsonContains('modules', 'vendors')
                ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return Category::query()
            ->whereIn('id', $categoryIds)
            ->whereNull('parent_id')
            ->whereJsonContains('modules', 'vendors')
            ->with(['children' => function ($query) use ($vendor) {
                $subcategoryIds = VendorProduct::query()
                    ->where('vendor_id', $vendor->id)
                    ->where('status', 'approved')
                    ->whereNotNull('subcategory_id')
                    ->pluck('subcategory_id')
                    ->unique()
                    ->filter();

                $query
                    ->when($subcategoryIds->isNotEmpty(), fn ($q) => $q->whereIn('id', $subcategoryIds))
                    ->orderBy('name')
                    ->select(['id', 'name', 'parent_id']);
            }])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return array{sponsoredFillers: array, sidebarAds: Collection, sectionAdRails: array<int, Collection>}
     */
    private function loadStoreAds(Vendor $vendor, int $sectionCount): array
    {
        if ($vendor->is_premium) {
            return [
                'sponsoredFillers' => [],
                'sidebarAds' => collect(),
                'sectionAdRails' => [],
            ];
        }

        $lat = session('frontend_lat');
        $lng = session('frontend_lng');
        $lat = is_numeric($lat) ? (float) $lat : null;
        $lng = is_numeric($lng) ? (float) $lng : null;

        $adsService = app(MarketplaceAdsService::class);
        $storeAds = $adsService->getDisplayAds(14, $lat, $lng);
        $split = $adsService->splitAdsForStoreLayout($storeAds, $sectionCount);

        return [
            'sponsoredFillers' => $adsService->getSponsoredFillers($lat, $lng),
            'sidebarAds' => $split['sidebar'],
            'sectionAdRails' => $split['section_rails'],
        ];
    }

    private function assertVendorCategory(Category $category, bool $isSubcategory = false): void
    {
        abort_unless(in_array('vendors', $category->modules ?? [], true), 404);

        if (! $isSubcategory) {
            abort_unless($category->parent_id === null, 404);
        }
    }
}
