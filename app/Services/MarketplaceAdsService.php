<?php

namespace App\Services;

use App\Models\UserAd;
use App\Support\AdSizes;
use App\Support\SocialShare;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MarketplaceAdsService
{
    public function getDisplayAds(
        int $limit = 12,
        ?float $lat = null,
        ?float $lng = null,
        array $preferredModules = [],
        bool $strictModules = false
    ): Collection
    {
        $adsQuery = UserAd::query()
            ->with(['category:id,name', 'subcategory:id,name', 'adSize:id,size_key,width,height'])
            ->where('status', 'approved')
            ->whereDoesntHave('adSize', fn (Builder $query) => $query->where('admin_only', true))
            ->whereNotNull('final_image')
            ->where(function (Builder $query) {
                $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()->toDateString());
            });

        if ($preferredModules !== []) {
            $adsQuery->where(function (Builder $query) use ($preferredModules, $strictModules) {
                foreach ($preferredModules as $module) {
                    $query->orWhere(function (Builder $moduleQuery) use ($module, $strictModules): void {
                        if ($strictModules) {
                            $moduleQuery->selectedForModule((string) $module);

                            return;
                        }

                        $moduleQuery->assignedToModule((string) $module);
                    });
                }
            });
        }

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

        return $adsQuery->take($limit)->get()->values();
    }

    /**
     * @return array<int, array{id:int, w:int, h:int, label:string, title:?string, image:?string, url:?string}>
     */
    public function getSponsoredFillers(?float $lat, ?float $lng, array $preferredModules = [], bool $strictModules = false): array
    {
        $requiredDimensions = collect(AdSizes::sponsoredFillerDimensions())
            ->flip();

        $sponsoredAdsQuery = UserAd::query()
            ->where('status', 'approved')
            ->where('is_sponsored', true)
            ->whereNotNull('final_image')
            ->where(function (Builder $query) {
                $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', now()->toDateString());
            });

        if ($preferredModules !== []) {
            $sponsoredAdsQuery->where(function (Builder $query) use ($preferredModules, $strictModules) {
                foreach ($preferredModules as $module) {
                    $query->orWhere(function (Builder $moduleQuery) use ($module, $strictModules): void {
                        if ($strictModules) {
                            $moduleQuery->selectedForModule((string) $module);

                            return;
                        }

                        $moduleQuery->assignedToModule((string) $module);
                    });
                }
            });
        }

        $sponsoredAds = $sponsoredAdsQuery
            ->orderByDesc('reviewed_at')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get(['id', 'title', 'size_type', 'final_image', 'reviewed_at', 'location_lat', 'location_lng']);

        return $sponsoredAds
            ->map(function (UserAd $ad) use ($requiredDimensions) {
                $dims = AdSizes::dimensionsForSizeType((string) $ad->size_type);
                if (! $dims || $dims['w'] <= 0 || $dims['h'] <= 0) {
                    return null;
                }

                $sizeConfig = AdSizes::all(true)[(string) $ad->size_type] ?? null;
                if (! $sizeConfig || ! AdSizes::isSponsoredFillerSize($sizeConfig)) {
                    return null;
                }

                $sizeKey = $dims['w'].'x'.$dims['h'];
                if (! $requiredDimensions->has($sizeKey)) {
                    return null;
                }

                return [
                    'id' => $ad->id,
                    'size_key' => (string) $ad->size_type,
                    'name' => AdSizes::label((string) $ad->size_type),
                    'w' => $dims['w'],
                    'h' => $dims['h'],
                    'label' => 'Sponsored',
                    'title' => $ad->title,
                    'image' => asset($ad->final_image),
                    'url' => SocialShare::normalizeUrl(route('frontend.ads.show', ['ad' => $ad->getRouteKey()])),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * Split service-page ads into their intended placements. The standard
     * square format is intentionally omitted, full-page formats are reserved for the main
     * slider, and every other format is available to the supporting ad grid.
     *
     * @return array{full_page: Collection, supporting: Collection}
     */
    public function splitServicePageAds(Collection $ads): array
    {
        $visibleAds = $ads
            ->filter(fn (UserAd $ad): bool => ! $this->isStandardSquareAd($ad))
            ->values();

        return [
            'full_page' => $visibleAds->filter(fn (UserAd $ad): bool => $this->isFullPageAd($ad))->values(),
            'supporting' => $visibleAds->reject(fn (UserAd $ad): bool => $this->isFullPageAd($ad))->values(),
        ];
    }

    private function normalizedAdSizeKey(UserAd $ad): string
    {
        $sizeKey = (string) ($ad->adSize?->size_key ?: $ad->size_type);

        return strtolower(str_replace([' ', '-'], '_', trim($sizeKey)));
    }

    private function isStandardSquareAd(UserAd $ad): bool
    {
        return $this->normalizedAdSizeKey($ad) === 'square';
    }

    private function isFullPageAd(UserAd $ad): bool
    {
        return in_array($this->normalizedAdSizeKey($ad), ['full_page', 'full_size'], true);
    }

    /**
     * @return array<string, array{ads: \Illuminate\Support\Collection, grid_id: string}>
     */
    public function buildRandomPlacements(Collection $ads, int $sectionCount): array
    {
        if ($ads->isEmpty()) {
            return [];
        }

        $chunkSize = max(3, (int) ceil($ads->count() / 3));
        $chunks = $ads->chunk($chunkSize)->values();

        $slots = collect(['after_hero'])
            ->merge(collect(range(0, max(0, $sectionCount - 1)))->map(fn (int $i) => "after_section_{$i}"))
            ->push('before_products')
            ->shuffle()
            ->take($chunks->count())
            ->values();

        $placements = [];

        foreach ($slots as $index => $slot) {
            $chunk = $chunks[$index] ?? null;
            if (! $chunk || $chunk->isEmpty()) {
                continue;
            }

            $placements[$slot] = [
                'ads' => $chunk->values(),
                'grid_id' => 'storeAds'.$index,
            ];
        }

        return $placements;
    }

    /**
     * @return array{sidebar: Collection, section_rails: array<int, Collection>}
     */
    public function splitAdsForStoreLayout(Collection $ads, int $sectionCount): array
    {
        $ads = $ads->values();

        if ($ads->isEmpty()) {
            return ['sidebar' => collect(), 'section_rails' => []];
        }

        $sidebarCount = min(10, max(4, (int) ceil($ads->count() * 0.65)));
        $sidebar = $ads->take($sidebarCount)->values();
        $remaining = $ads->slice($sidebarCount)->values();

        $sectionRails = [];

        if ($sectionCount > 0 && $remaining->isNotEmpty()) {
            $perSection = max(1, (int) ceil($remaining->count() / $sectionCount));

            foreach (range(0, $sectionCount - 1) as $index) {
                $chunk = $remaining->slice($index * $perSection, $perSection)->values();

                if ($chunk->isNotEmpty()) {
                    $sectionRails[$index] = $chunk;
                }
            }
        }

        return [
            'sidebar' => $sidebar,
            'section_rails' => $sectionRails,
        ];
    }
}
