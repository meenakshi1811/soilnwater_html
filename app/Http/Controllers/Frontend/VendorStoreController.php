<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\VendorProduct;
use App\Models\Vendor;
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
}
