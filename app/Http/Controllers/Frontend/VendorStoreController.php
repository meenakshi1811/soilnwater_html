<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\VendorProduct;
use App\Models\Vendor;
use App\Models\UserAd;
use App\Models\VendorProductInquiry;
use App\Support\AdSizes;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VendorStoreController extends Controller
{
    public function show(Request $request, string $slug): View|JsonResponse
    {
        $lat = session('frontend_lat');
        $lng = session('frontend_lng');

        $vendor = Vendor::query()
            ->where('slug', $slug)
            ->where('status', 'approved')
            ->with(['bannerSlides', 'pageSections'])
            ->firstOrFail();

        $products = VendorProduct::query()
            ->where('vendor_id', $vendor->id)
            ->where('status', 'approved')
            ->when(is_numeric($lat) && is_numeric($lng), function ($query) use ($lat, $lng) {
                $query
                    ->select('vendor_products.*')
                    ->selectRaw('CASE WHEN latitude IS NOT NULL AND longitude IS NOT NULL THEN (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) ELSE NULL END as distance_km', [(float) $lat, (float) $lng, (float) $lat])
                    ->orderByRaw('CASE WHEN distance_km IS NULL THEN 1 ELSE 0 END')
                    ->orderBy('distance_km');
            }, function ($query) {
                $query->orderByDesc('updated_at');
            })
            ->paginate(12);

        if ($request->ajax()) {
            $html = view('frontend.store.partials.product-cards', [
                'products' => $products,
            ])->render();

            return response()->json([
                'html' => $html,
                'next_page' => $products->hasMorePages() ? ($products->currentPage() + 1) : null,
            ]);
        }

        return view('frontend.store.show', [
            'vendor' => $vendor,
            'preview' => false,
            'products' => $products,
        ]);
    }

    public function productShow(string $slug, VendorProduct $product): View
    {
        $vendor = Vendor::query()->where('slug', $slug)->where('status', 'approved')->firstOrFail();
        abort_unless($product->vendor_id === $vendor->id && $product->status === 'approved', 404);

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
