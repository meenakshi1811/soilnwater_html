<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\VendorProduct;
use App\Models\Vendor;
use App\Models\UserAd;
use App\Models\VendorProductInquiry;
use App\Support\AdSizes;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\View\View;

class VendorStoreController extends Controller
{
    public function show(string $slug): View
    {
        return $this->renderStorePage($slug);
    }

    public function showByCategory(string $slug, Category $category): View
    {
        return $this->renderStorePage($slug, $category->id, null);
    }

    public function showBySubcategory(string $slug, Category $category, Category $subcategory): View
    {
        abort_unless($subcategory->parent_id === $category->id, 404);

        return $this->renderStorePage($slug, $category->id, $subcategory->id);
    }

    private function renderStorePage(string $slug, ?int $categoryId = null, ?int $subcategoryId = null): View
    {
        $vendor = Vendor::query()
            ->where('slug', $slug)
            ->where('status', 'approved')
            ->with(['bannerSlides', 'pageSections'])
            ->firstOrFail();

        $vendorCategories = Category::query()
            ->whereNull('parent_id')
            ->whereJsonContains('modules', 'products')
            ->whereHas('children', function ($query) use ($vendor) {
                $query->whereHas('vendorProducts', function ($productQuery) use ($vendor) {
                    $productQuery->where('vendor_id', $vendor->id)->where('status', 'approved');
                });
            })
            ->with(['children' => function ($query) use ($vendor) {
                $query->whereHas('vendorProducts', function ($productQuery) use ($vendor) {
                    $productQuery->where('vendor_id', $vendor->id)->where('status', 'approved');
                })->orderBy('name');
            }])
            ->orderBy('name')
            ->get();

        $products = VendorProduct::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'approved')
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->when($subcategoryId, fn ($query) => $query->where('subcategory_id', $subcategoryId))
            ->latest('updated_at')
            ->limit(8)
            ->get();

        return view('frontend.store.show', [
            'vendor' => $vendor,
            'preview' => false,
            'products' => $products,
            'vendorCategories' => $vendorCategories,
            'activeCategoryId' => $categoryId,
            'activeSubcategoryId' => $subcategoryId,
        ]);
    }

    public function productShow(string $slug, VendorProduct $product): View
    {
        $vendor = Vendor::query()->where('slug', $slug)->where('status', 'approved')->firstOrFail();
        abort_unless($product->vendor_id === $vendor->id && $product->status === 'approved', 404);

        if ($vendor->is_premium) {
            $topGroups = collect();
            $sideGroups = collect();
            $bottomGroups = collect();
            $ads = collect();

            return view('frontend.store.product-show', compact('vendor', 'product', 'topGroups', 'sideGroups', 'bottomGroups', 'ads'));
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

            // Strict match: top slot is only for explicit full_page size.
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

        return view('frontend.store.product-show', compact('vendor', 'product', 'topGroups', 'sideGroups', 'bottomGroups', 'ads'));
    }

    public function sendInquiry(Request $request, string $slug, VendorProduct $product): JsonResponse
    {
        $vendor = Vendor::query()->where('slug', $slug)->where('status', 'approved')->firstOrFail();
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

}
