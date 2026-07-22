<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomepageSetting;
use App\Models\UserAd;
use App\Services\MarketplaceAdsService;
use App\Support\AdSizes;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdsMarketController extends Controller
{
    public function __construct(private MarketplaceAdsService $marketplaceAdsService) {}

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
        $sponsoredFillers = $this->marketplaceAdsService->getSponsoredFillers($lat, $lng);
        $sponsoredBlankSizes = AdSizes::sponsoredFillerSizesFromDatabase();

        $adsQuery = UserAd::query()
            ->with(['category:id,name', 'subcategory:id,name', 'adSize:id,size_key,width,height'])
            ->where('status', 'approved')
            ->whereDoesntHave('adSize', fn (Builder $query) => $query->where('admin_only', true))
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
        $selectedCategoryNamesByAdId = $this->resolveSelectedCategoryNamesByAdId($ads->items());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('frontend.ads.partials.cards', [
                    'ads' => $ads,
                    'sponsoredFillers' => $sponsoredFillers,
                    'sponsoredBlankSizes' => $sponsoredBlankSizes,
                    'selectedCategoryNamesByAdId' => $selectedCategoryNamesByAdId,
                ])->render(),
                'next_page_url' => $ads->nextPageUrl(),
                'loaded_to' => $ads->lastItem() ?? 0,
                'total' => $ads->total(),
            ]);
        }

        $homepageSetting = HomepageSetting::query()->find(1);

        return view('frontend.ads.index', compact('ads', 'categories', 'categoriesForFilter', 'sponsoredFillers', 'sponsoredBlankSizes', 'homepageSetting', 'selectedCategoryNamesByAdId'));
    }

    public function show(UserAd $ad): View
    {
        abort_unless($ad->status === 'approved' && $ad->final_image && (! $ad->valid_until || $ad->valid_until->isToday() || $ad->valid_until->isFuture()), 404);

        $ad->load(['category:id,name', 'subcategory:id,name']);

        $selectedCategoryLabels = Category::query()
            ->whereIn('id', array_map('intval', $ad->selected_category_ids ?? []))
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        $selectedSubcategoryLabels = Category::query()
            ->whereIn('id', array_map('intval', $ad->selected_subcategory_ids ?? []))
            ->orderBy('name')
            ->pluck('name')
            ->values()
            ->all();

        if ($selectedCategoryLabels === [] && $ad->category?->name) {
            $selectedCategoryLabels = [$ad->category->name];
        }

        if ($selectedSubcategoryLabels === [] && $ad->subcategory?->name) {
            $selectedSubcategoryLabels = [$ad->subcategory->name];
        }

        return view('frontend.ads.show', [
            'ad' => $ad,
            'selectedCategoryLabels' => $selectedCategoryLabels,
            'selectedSubcategoryLabels' => $selectedSubcategoryLabels,
        ]);
    }

    private function resolveSelectedCategoryNamesByAdId(array $ads): array
    {
        $selectedCategoryIds = collect($ads)
            ->flatMap(fn (UserAd $ad) => array_map('intval', $ad->selected_category_ids ?? []))
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        $categoryNamesById = Category::query()
            ->whereIn('id', $selectedCategoryIds)
            ->pluck('name', 'id');

        return collect($ads)
            ->mapWithKeys(function (UserAd $ad) use ($categoryNamesById) {
                $selectedNames = collect($ad->selected_category_ids ?? [])
                    ->map(fn ($id) => $categoryNamesById->get((int) $id))
                    ->filter()
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
