<?php

namespace App\Support;

use App\Models\ServiceProvider;
use Illuminate\Support\Str;

class ServiceProviderListingCard
{
    /**
     * @return array{
     *     coverImage:string,
     *     avatarImage:string,
     *     profileUrl:string,
     *     hasLocation:bool,
     *     locationLabel:string,
     *     categoryName:string,
     *     featuredLabel:?string,
     *     serviceTags:array<int, string>,
     *     ratingScore:float,
     *     ratingCount:int
     * }
     */
    public static function data(ServiceProvider $serviceProvider, bool $hasLocation = false): array
    {
        $firstService = $serviceProvider->services->first();
        $serviceImage = $firstService?->image_path ? asset($firstService->image_path) : null;
        $bannerImage = $serviceProvider->bannerSlides->first()?->image_path ? asset($serviceProvider->bannerSlides->first()->image_path) : null;
        $logoImage = $serviceProvider->logo ? asset($serviceProvider->logo) : null;
        $primaryBranch = $serviceProvider->branches->first();
        $branchLogo = $primaryBranch?->logo ? asset($primaryBranch->logo) : null;
        $coverImage = $bannerImage ?? $serviceImage ?? $logoImage ?? $branchLogo ?? asset('assets/images/vendor-card-placeholder.svg');
        $avatarImage = $logoImage ?? $branchLogo ?? $serviceImage ?? asset('assets/images/profile-placeholder.svg');
        $providerLocation = $primaryBranch?->city ?: ($serviceProvider->city ?: 'Local Area');
        $providerState = $primaryBranch?->state ?: ($serviceProvider->state ?? null);
        $locationLabel = $providerState ? $providerLocation.', '.$providerState : $providerLocation;
        $categoryName = $firstService?->categoryModel?->name
            ?: $firstService?->subcategoryModel?->name
            ?: $firstService?->category
            ?: 'General';
        $featuredLabel = filled($firstService?->name)
            ? $firstService->name
            : (Str::limit(strip_tags((string) $serviceProvider->description), 72) ?: null);
        $serviceCount = (int) ($serviceProvider->services_count ?? $serviceProvider->services->count());
        $ratingScore = min(5, round(4 + min($serviceCount, 40) * 0.025, 1));
        $ratingCount = max($serviceCount, 1);

        return [
            'coverImage' => $coverImage,
            'avatarImage' => $avatarImage,
            'profileUrl' => route('service_provider.show', $serviceProvider->slug),
            'hasLocation' => $hasLocation,
            'locationLabel' => $locationLabel,
            'categoryName' => $categoryName,
            'featuredLabel' => $featuredLabel,
            'serviceTags' => $serviceProvider->services->take(3)->pluck('name')->filter()->values()->all(),
            'ratingScore' => $ratingScore,
            'ratingCount' => $ratingCount,
        ];
    }
}
