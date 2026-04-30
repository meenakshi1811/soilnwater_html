<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\UserAd;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdsMarketController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $categories = Category::query()
            ->whereNull('parent_id')
            ->whereJsonContains('modules', 'ads')
            ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id'])])
            ->orderBy('name')
            ->get(['id', 'name']);

        $categoriesForFilter = $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'children' => $category->children->map(function ($child) {
                    return ['id' => $child->id, 'name' => $child->name, 'parent_id' => $child->parent_id];
                })->values()->all(),
            ];
        })->values()->all();

        $lat = $request->filled('lat') ? (float) $request->input('lat') : session('frontend_lat');
        $lng = $request->filled('lng') ? (float) $request->input('lng') : session('frontend_lng');

        $adsQuery = UserAd::query()
            ->with(['category:id,name', 'subcategory:id,name'])
            ->where('status', 'approved')
            ->whereHas('user', fn (Builder $query) => $query->where('role', 'user'))
            ->whereNotNull('final_image')
            ->when($request->filled('category_id'), fn (Builder $query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('subcategory_id'), fn (Builder $query) => $query->where('subcategory_id', $request->integer('subcategory_id')))
            ->when($request->filled('search'), fn (Builder $query) => $query->where('title', 'like', '%'.$request->string('search')->toString().'%'));

        if ($lat !== null && $lng !== null) {
            $adsQuery
                ->select('user_ads.*')
                ->selectRaw('CASE WHEN location_lat IS NOT NULL AND location_lng IS NOT NULL THEN (6371 * acos(cos(radians(?)) * cos(radians(location_lat)) * cos(radians(location_lng) - radians(?)) + sin(radians(?)) * sin(radians(location_lat)))) ELSE NULL END as distance_km', [$lat, $lng, $lat])
                ->orderByRaw('CASE WHEN distance_km IS NULL THEN 1 ELSE 0 END')
                ->orderBy('distance_km');
        } else {
            $adsQuery->latest('reviewed_at');
        }

        $ads = $adsQuery
            ->latest('id')
            ->paginate(12)
            ->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('frontend.ads.partials.cards', ['ads' => $ads])->render(),
                'next_page_url' => $ads->nextPageUrl(),
                'loaded_to' => $ads->lastItem() ?? 0,
                'total' => $ads->total(),
            ]);
        }

        return view('frontend.ads.index', compact('ads', 'categories', 'categoriesForFilter'));
    }

    public function show(UserAd $ad): View
    {
        abort_unless($ad->status === 'approved' && $ad->final_image, 404);

        return view('frontend.ads.show', ['ad' => $ad->load(['category:id,name', 'subcategory:id,name'])]);
    }
}
