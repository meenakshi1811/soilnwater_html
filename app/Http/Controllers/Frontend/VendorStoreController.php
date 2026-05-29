<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\VendorProduct;
use App\Models\VendorProductInquiry;
use App\Models\UserAd;
use App\Models\User;
use App\Services\MarketplaceAdsService;
use App\Support\AdSizes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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
        $vendorRecentAds = $this->nearestVendorModuleAds();

        return view('frontend.store.show', [
            'vendor' => $vendor,
            'preview' => false,
            'activeNav' => 'home',
            'featuredProducts' => $featuredProducts,
            'vendorCategories' => $vendorCategories,
            'vendorRecentAds' => $vendorRecentAds,
            'selectedCategoryNamesByVendorAdId' => $this->resolveSelectedCategoryNamesByAdId($vendorRecentAds),
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
        if ($product !== null) {
            abort_unless($product->vendor_id === $vendor->id && $product->status === 'approved', 404);
        }

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

        $adsPool = $adsQuery->with('adSize:id,size_key,width,height')->take(36)->get();

        $productCategoryIds = collect([
            $product->category_id,
            $product->subcategory_id,
        ])->filter()->map(fn ($id) => (int) $id);

        $categoryMatchedAds = $adsPool
            ->filter(function (UserAd $ad) use ($productCategoryIds): bool {
                if ($productCategoryIds->isEmpty()) {
                    return false;
                }

                $selectedCategoryIds = collect($ad->selected_category_ids ?? [])->map(fn ($id) => (int) $id);
                $selectedSubcategoryIds = collect($ad->selected_subcategory_ids ?? [])->map(fn ($id) => (int) $id);

                return $selectedCategoryIds->intersect($productCategoryIds)->isNotEmpty()
                    || $selectedSubcategoryIds->intersect($productCategoryIds)->isNotEmpty();
            })
            ->values();

        $vendorModuleAds = $adsPool
            ->filter(function (UserAd $ad): bool {
                $selectedModules = collect($ad->selected_modules ?? [])->map(fn ($module) => strtolower((string) $module));

                return $selectedModules->contains('vendors');
            })
            ->values();

        $ads = $categoryMatchedAds->isNotEmpty() ? $categoryMatchedAds : $vendorModuleAds;

        if ($ads->isEmpty()) {
            $ads = $adsPool;
        }

        if ($ads->isEmpty()) {
            $ads = UserAd::query()
                ->with('adSize:id,size_key,width,height')
                ->where('status', 'approved')
                ->whereNotNull('final_image')
                ->whereDoesntHave('adSize', fn ($query) => $query->where('admin_only', true))
                ->where(function ($query) {
                    $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()->toDateString());
                })
                ->inRandomOrder()
                ->take(18)
                ->get();
        }

        $ads = $ads->take(18)->shuffle()->values();

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


    public function contact(string $slug): View
    {
        $vendor = $this->resolveVendor($slug);
        $inquiryProduct = VendorProduct::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'approved')
            ->latest('updated_at')
            ->first();

        return view('frontend.store.contact', [
            'vendor' => $vendor,
            'preview' => false,
            'activeNav' => 'contact',
            'vendorCategories' => $this->vendorCategories($vendor),
            'inquiryProduct' => $inquiryProduct,
        ]);
    }

    public function about(string $slug): View
    {
        $vendor = $this->resolveVendor($slug);

        return view('frontend.store.about', [
            'vendor' => $vendor,
            'preview' => false,
            'activeNav' => 'about',
            'vendorCategories' => $this->vendorCategories($vendor),
        ]);
    }


    public function sendGeneralInquiry(Request $request, string $slug): JsonResponse
    {
        return $this->sendInquiry($request, $slug, null);
    }

    public function sendInquiry(Request $request, string $slug, ?VendorProduct $product = null): JsonResponse
    {
        $vendor = $this->resolveVendor($slug);
        if ($product !== null) {
            abort_unless($product->vendor_id === $vendor->id && $product->status === 'approved', 404);
        }

        if (! $request->user()) {
            return response()->json(['message' => 'Please login to send an enquiry.'], 403);
        }

        $data = $request->validate([
            'email' => ['required', 'email'],
            'phone_number' => ['required', 'string', 'max:20'],
            'preferred_contact' => ['required', 'in:text,whatsapp,call,email'],
            'reason' => ['required', 'string', 'max:2000'],
        ]);

        $productId = $request->routeIs('store.enquiry') ? null : $product?->id;

        $inquiry = VendorProductInquiry::query()->create([
            'vendor_id' => $vendor->id,
            'vendor_product_id' => $productId,
            'user_id' => $request->user()->id,
            ...$data,
        ]);

        if ($vendor->email) {
            $body = view('emails.vendor.new-inquiry', compact('inquiry', 'vendor', 'product'))->render();
            Mail::send([], [], function ($message) use ($vendor, $product, $productId, $body) {
                $subject = 'New product inquiry'.($productId ? ': '.$product->name : '');
                $message->to($vendor->email)->subject($subject)->html($body);
            });
        }

        $this->sendVendorInquirySms($vendor, $productId ? $product : null);
        return response()->json(['message' => 'Enquiry sent successfully.']);
    }

   private function sendVendorInquirySms(Vendor $vendor, ?VendorProduct $product = null): void
    {
        try {
            $user = User::select('email', 'phone_number')
            ->where('id', $vendor->user_id)
            ->first();
            $phoneNumber = $user->phone_number;

            if (! $phoneNumber) {
                return;
            }

            $apikey = config('services.message.api_key');
            $username = config('services.message.username');
            $sender = config('services.message.sender', 'ANNUVE');
            $smstype = config('services.message.smstype');
            $peid = config('services.message.peid');

            $message = sprintf(
                'Hello %s, A new inquiry has been submitted for %s. Please log in to your vendor account to check and respond to the inquiry. Thank you – Annuvedant Team',
                $vendor->publicDisplayName(),
                $product?->name ?? 'your store'
            );
            
            // $message = "Verification OTP Your login verification code is {$phoneOtpCode} This code is valid for 5 minutes. Do not share it with anyone. – Annuvedant Team";


            $url = 'http://sms.messageindia.in/v2/sendSMS?' . http_build_query([
                'username' => $username,
                'message' => $message,
                'sendername' => $sender,
                'smstype' => $smstype,
                'numbers' => $phoneNumber,
                'apikey' => $apikey,
                'peid' => $peid,
                'templateid' => 1707177936224680013,
            ]);

            // echo'<pre>';print_r($url);exit();
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ]);

            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                Log::error('Vendor inquiry SMS failed', [
                    'phone' => $phoneNumber,
                    'error' => curl_error($curl),
                ]);

                curl_close($curl);

                return;
            }

            curl_close($curl);

            Log::info('Vendor inquiry SMS sent successfully', [
                'phone' => $phoneNumber,
                'response' => $response,
            ]);

        } catch (\Throwable $exception) {
            Log::error('Exception while sending vendor inquiry SMS', [
                'phone' => $vendor->phone_number ?? null,
                'product_id' => $product->id ?? null,
                'message' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
            ]);
        }
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
        $adsContext = $this->loadStoreAds($vendor, 0, $category, $subcategory);

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

    private function nearestVendorModuleAds(int $limit = 20): Collection
    {
        $lat = session('frontend_lat');
        $lng = session('frontend_lng');
        $lat = is_numeric($lat) ? (float) $lat : null;
        $lng = is_numeric($lng) ? (float) $lng : null;

        $adsQuery = UserAd::query()
            ->with(['category:id,name'])
            ->where('status', 'approved')
            ->whereJsonContains('selected_modules', 'vendors')
            ->whereDoesntHave('adSize', fn ($query) => $query->where('admin_only', true))
            ->whereNotNull('final_image')
            ->where(function ($query) {
                $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()->toDateString());
            });

        if ($lat !== null && $lng !== null) {
            $adsQuery
                ->select('user_ads.*')
                ->selectRaw('CASE WHEN location_lat IS NOT NULL AND location_lng IS NOT NULL THEN (6371 * acos(cos(radians(?)) * cos(radians(location_lat)) * cos(radians(location_lng) - radians(?)) + sin(radians(?)) * sin(radians(location_lat)))) ELSE NULL END as distance_km', [$lat, $lng, $lat])
                ->orderByRaw('CASE WHEN distance_km IS NULL THEN 1 ELSE 0 END')
                ->orderBy('distance_km');
        }

        return $adsQuery
            ->latest('created_at')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

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

    /**
     * @return array{sponsoredFillers: array, sidebarAds: Collection, sectionAdRails: array<int, Collection>}
     */
    private function loadStoreAds(Vendor $vendor, int $sectionCount, ?Category $category = null, ?Category $subcategory = null): array
    {
        if ($vendor->is_premium) {
            return [
                'sponsoredFillers' => [],
                'sidebarAds' => collect(),
                'sectionAdRails' => [],
                'randomFullPagePlacements' => [],
            ];
        }

        $lat = session('frontend_lat');
        $lng = session('frontend_lng');
        $lat = is_numeric($lat) ? (float) $lat : null;
        $lng = is_numeric($lng) ? (float) $lng : null;

        $adsService = app(MarketplaceAdsService::class);
        $storeAds = $adsService->getDisplayAds(24, $lat, $lng, ['vendors']);

        $requestedCategoryIds = collect([
            $category?->id,
            $subcategory?->id,
        ])->filter()->map(fn ($id) => (int) $id)->values();

        $vendorModuleAds = $storeAds
            ->filter(function (UserAd $ad): bool {
                $selectedModules = collect($ad->selected_modules ?? [])->map(fn ($module) => strtolower((string) $module));

                return $selectedModules->contains('vendors');
            })
            ->values();

        $categoryMatchedAds = $vendorModuleAds;

        if ($requestedCategoryIds->isNotEmpty()) {
            $categoryMatchedAds = $vendorModuleAds
                ->filter(function (UserAd $ad) use ($requestedCategoryIds): bool {
                    $selectedCategoryIds = collect($ad->selected_category_ids ?? [])->map(fn ($id) => (int) $id);
                    $selectedSubcategoryIds = collect($ad->selected_subcategory_ids ?? [])->map(fn ($id) => (int) $id);

                    return $selectedCategoryIds->intersect($requestedCategoryIds)->isNotEmpty()
                        || $selectedSubcategoryIds->intersect($requestedCategoryIds)->isNotEmpty();
                })
                ->values();
        }

        $effectiveAds = $categoryMatchedAds->isNotEmpty() ? $categoryMatchedAds : $vendorModuleAds;

        if ($effectiveAds->isEmpty()) {
            $effectiveAds = $adsService->getDisplayAds(14, $lat, $lng);
        }

        if ($effectiveAds->isEmpty()) {
            $effectiveAds = UserAd::query()
                ->with(['category:id,name', 'subcategory:id,name', 'adSize:id,size_key,width,height'])
                ->where('status', 'approved')
                ->whereNotNull('final_image')
                ->where(function ($query) {
                    $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()->toDateString());
                })
                ->inRandomOrder()
                ->take(14)
                ->get();
        }

        $effectiveAds = $effectiveAds->shuffle()->values();
        $split = $adsService->splitAdsForStoreLayout($effectiveAds, $sectionCount);
        $randomFullPagePlacements = $adsService->buildRandomPlacements($effectiveAds, $sectionCount);

        return [
            'sponsoredFillers' => $adsService->getSponsoredFillers($lat, $lng),
            'sidebarAds' => $split['sidebar'],
            'sectionAdRails' => $split['section_rails'],
            'randomFullPagePlacements' => $randomFullPagePlacements,
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
