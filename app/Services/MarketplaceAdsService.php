<?php

namespace App\Services;

use App\Models\UserAd;
use App\Support\AdSizes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class MarketplaceAdsService
{
    public function getDisplayAds(
        int $limit = 12,
        ?float $lat = null,
        ?float $lng = null,
        array $preferredModules = []
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
            $adsQuery->where(function (Builder $query) use ($preferredModules) {
                foreach ($preferredModules as $module) {
                    $query->orWhereJsonContains('selected_modules', (string) $module);
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

        return $adsQuery->take($limit)->get()->shuffle()->values();
    }

    /**
     * @return array<int, array{w:int, h:int, label:string, title:?string, image:?string, url:?string}>
     */
    public function getSponsoredFillers(?float $lat, ?float $lng): array
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
            ->get(['id', 'title', 'size_type', 'final_image', 'reviewed_at', 'location_lat', 'location_lng']);

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
