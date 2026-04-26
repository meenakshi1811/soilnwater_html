<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Offer;
use App\Models\UserAd;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class OfferPageController extends Controller
{
    public function home(): View
    {
        $offers = $this->baseOfferQuery()
            ->limit(10)
            ->get();

        $frontPageAds = UserAd::query()
            ->where('status', 'approved')
            ->whereIn('size_type', ['top_categories_ad_1', 'top_categories_ad_2'])
            ->whereNotNull('final_image')
            ->latest('reviewed_at')
            ->latest('id')
            ->get(['id', 'title', 'size_type', 'final_image']);

        return view('frontend.index', [
            'offers' => $offers,
            'topCategoriesSliderAds' => $frontPageAds->where('size_type', 'top_categories_ad_1')->values(),
            'topSidebarSliderAds' => $frontPageAds->where('size_type', 'top_categories_ad_2')->values(),
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

        $offers = $this->baseOfferQuery($request)->paginate(12)->appends($request->query());

        if ($request->ajax()) {
            return response()->json([
                'html' => view('frontend.offers.partials.cards', ['offers' => $offers])->render(),
                'next_page_url' => $offers->nextPageUrl(),
                'loaded_to' => $offers->lastItem() ?? 0,
                'total' => $offers->total(),
            ]);
        }

        return view('frontend.offers.index', [
            'offers' => $offers,
            'categories' => $categories,
            'categoriesForFilter' => $categoriesForFilter,
        ]);
    }

    public function show(Offer $offer): View
    {
        abort_unless($this->isPublished($offer), 404);

        return view('frontend.offers.show', [
            'offer' => $offer,
        ]);
    }

    private function baseOfferQuery(?Request $request = null): Builder
    {
        $today = now()->toDateString();
        $request = $request ?? request();

        return Offer::query()
            ->where('status', 'active')
            ->when($request->filled('category_id'), fn (Builder $query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('subcategory_id'), fn (Builder $query) => $query->where('subcategory_id', $request->integer('subcategory_id')))
            ->when($request->filled('validity'), function (Builder $query) use ($request): void {
                $this->applyValidityFilter($query, $request->string('validity')->toString(), Carbon::today());
            }, function (Builder $query): void {
                $this->applyValidityFilter($query, 'valid', Carbon::today());
            })
            ->orderByRaw('CASE WHEN valid_until = ? THEN 0 ELSE 1 END', [$today])
            ->orderByRaw('CASE WHEN valid_until IS NULL THEN 1 ELSE 0 END')
            ->orderBy('valid_until')
            ->latest('id');
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
}
