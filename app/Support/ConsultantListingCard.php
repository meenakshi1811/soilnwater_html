<?php

namespace App\Support;

use App\Models\Consultant;
use Illuminate\Support\Str;

class ConsultantListingCard
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
    public static function data(Consultant $consultant, bool $hasLocation = false): array
    {
        $firstService = $consultant->services->first();
        $serviceImage = $firstService?->image_path ? asset($firstService->image_path) : null;
        $bannerImage = $consultant->bannerSlides->first()?->image_path ? asset($consultant->bannerSlides->first()->image_path) : null;
        $logoImage = $consultant->logo ? asset($consultant->logo) : null;
        $primaryBranch = $consultant->branches->first();
        $branchLogo = $primaryBranch?->logo ? asset($primaryBranch->logo) : null;
        $coverImage = $bannerImage ?? $serviceImage ?? $logoImage ?? $branchLogo ?? asset('assets/images/vendor-card-placeholder.svg');
        $avatarImage = $logoImage ?? $branchLogo ?? $serviceImage ?? asset('assets/images/profile-placeholder.svg');
        $consultantLocation = $primaryBranch?->city ?: ($consultant->city ?: 'Local Area');
        $consultantState = $primaryBranch?->state ?: ($consultant->state ?? null);
        $locationLabel = $consultantState ? $consultantLocation.', '.$consultantState : $consultantLocation;
        $categoryName = $firstService?->categoryModel?->name
            ?: $firstService?->subcategoryModel?->name
            ?: $firstService?->category
            ?: 'General';
        $featuredLabel = filled($firstService?->name)
            ? $firstService->name
            : (Str::limit(strip_tags((string) $consultant->description), 72) ?: null);
        $serviceCount = (int) ($consultant->services_count ?? $consultant->services->count());
        $ratingScore = min(5, round(4 + min($serviceCount, 40) * 0.025, 1));
        $ratingCount = max($serviceCount, 1);

        return [
            'coverImage' => $coverImage,
            'avatarImage' => $avatarImage,
            'profileUrl' => route('consultant.show', $consultant->slug),
            'hasLocation' => $hasLocation,
            'locationLabel' => $locationLabel,
            'categoryName' => $categoryName,
            'featuredLabel' => $featuredLabel,
            'serviceTags' => $consultant->services->take(3)->pluck('name')->filter()->values()->all(),
            'ratingScore' => $ratingScore,
            'ratingCount' => $ratingCount,
        ];
    }
}
