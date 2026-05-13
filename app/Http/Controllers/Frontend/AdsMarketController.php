<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomepageSetting;
use App\Models\UserAd;
use App\Support\AdSizes;
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
        $sponsoredFillers = $this->getSponsoredFillers($lat, $lng);

        $adsQuery = UserAd::query()
            ->with(['category:id,name', 'subcategory:id,name', 'adSize:id,size_key,width,height'])
            ->where('status', 'approved')
            ->whereNotNull('final_image')
            ->where(function (Builder $query) {
                $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()->toDateString());
            })
            ->when($request->filled('category_id'), fn (Builder $query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('subcategory_id'), fn (Builder $query) => $query->where('subcategory_id', $request->integer('subcategory_id')))
            ->when($request->filled('search'), fn (Builder $query) => $query->where('title', 'like', '%'.$request->string('search')->toString().'%'));

        if ($lat !== null && $lng !== null) {
            $adsQuery
                ->select('user_ads.*')
                ->selectRaw('CASE WHEN location_lat IS NOT NULL AND location_lng IS NOT NULL THEN (6371 * acos(cos(radians(?)) * cos(radians(location_lat)) * cos(radians(location_lng) - radians(?)) + sin(radians(?)) * sin(radians(location_lat)))) ELSE NULL END as distance_km', [$lat, $lng, $lat])
                ->orderByRaw('CASE WHEN distance_km IS NULL THEN 1 ELSE 0 END')
                ->orderBy('distance_km')
                ->orderByDesc('updated_at');
        } else {
            $adsQuery->orderByDesc('updated_at');
        }

        $ads = $adsQuery
            ->paginate(12)
            ->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('frontend.ads.partials.cards', ['ads' => $ads, 'sponsoredFillers' => $sponsoredFillers])->render(),
                'next_page_url' => $ads->nextPageUrl(),
                'loaded_to' => $ads->lastItem() ?? 0,
                'total' => $ads->total(),
            ]);
        }

        $homepageSetting = HomepageSetting::query()->find(1);

        return view('frontend.ads.index', compact('ads', 'categories', 'categoriesForFilter', 'sponsoredFillers', 'homepageSetting'));
    }

    public function show(UserAd $ad): View
    {
        abort_unless($ad->status === 'approved' && $ad->final_image && (! $ad->valid_until || $ad->valid_until->isToday() || $ad->valid_until->isFuture()), 404);

        return view('frontend.ads.show', ['ad' => $ad->load(['category:id,name', 'subcategory:id,name'])]);
    }

    private function getSponsoredFillers(?float $lat, ?float $lng): array
    {
        $requiredSizes = collect([
            [458, 458], [458, 229], [229, 458], [229, 229], [520, 360], [520, 300], [458, 300], [360, 360], [320, 300],
        ])->mapWithKeys(fn (array $size) => [$size[0].'x'.$size[1] => ['w' => $size[0], 'h' => $size[1]]]);

        $sponsoredAds = UserAd::query()
            ->where('status', 'approved')
            ->where('is_sponsored', true)
            ->whereNotNull('final_image')
            ->where(function (Builder $query) {
                $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()->toDateString());
            })
            ->get(['id', 'size_type', 'final_image', 'reviewed_at', 'location_lat', 'location_lng']);

        $normalizedSizes = collect(AdSizes::all())->mapWithKeys(function ($size, $key) {
            $normalizedKey = strtolower(str_replace([' ', '-'], '_', (string) $key));

            return [$normalizedKey => ['w' => (int) ($size['w'] ?? 0), 'h' => (int) ($size['h'] ?? 0)]];
        });

        $pickedBySize = [];

        foreach ($sponsoredAds as $ad) {
            $sizeType = strtolower(str_replace([' ', '-'], '_', (string) $ad->size_type));
            $dims = $normalizedSizes[$sizeType] ?? null;
            if (! $dims || $dims['w'] <= 0 || $dims['h'] <= 0) {
                continue;
            }

            $sizeKey = $dims['w'].'x'.$dims['h'];
            if (! $requiredSizes->has($sizeKey)) {
                continue;
            }

            $distance = null;
            if ($lat !== null && $lng !== null && $ad->location_lat !== null && $ad->location_lng !== null) {
                $distance = 6371 * acos(
                    cos(deg2rad($lat)) * cos(deg2rad((float) $ad->location_lat)) * cos(deg2rad((float) $ad->location_lng) - deg2rad($lng))
                    + sin(deg2rad($lat)) * sin(deg2rad((float) $ad->location_lat))
                );
            }

            $current = $pickedBySize[$sizeKey] ?? null;
            if ($current === null) {
                $pickedBySize[$sizeKey] = ['ad' => $ad, 'distance' => $distance];
                continue;
            }

            $currentDistance = $current['distance'];
            $shouldReplace = false;

            if ($distance !== null && $currentDistance === null) {
                $shouldReplace = true;
            } elseif ($distance !== null && $currentDistance !== null && $distance < $currentDistance) {
                $shouldReplace = true;
            } elseif ($distance === $currentDistance && optional($ad->reviewed_at)->gt(optional($current['ad']->reviewed_at))) {
                $shouldReplace = true;
            } elseif ($distance === null && $currentDistance === null && optional($ad->reviewed_at)->gt(optional($current['ad']->reviewed_at))) {
                $shouldReplace = true;
            }

            if ($shouldReplace) {
                $pickedBySize[$sizeKey] = ['ad' => $ad, 'distance' => $distance];
            }
        }

        return $requiredSizes->map(function (array $size, string $sizeKey) use ($pickedBySize) {
            $picked = $pickedBySize[$sizeKey]['ad'] ?? null;

            return [
                'w' => $size['w'],
                'h' => $size['h'],
                'label' => 'Sponsored',
                'title' => $picked?->title,
                'image' => $picked?->final_image ? asset($picked->final_image) : null,
                'url' => $picked ? route('frontend.ads.show', $picked) : null,
            ];
        })->values()->all();
    }
}
