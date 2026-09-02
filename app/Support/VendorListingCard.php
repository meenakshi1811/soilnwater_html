<?php

namespace App\Support;

use App\Models\Vendor;
use Illuminate\Support\Str;

class VendorListingCard
{
    /**
     * @return array{
     *     coverImage:string,
     *     avatarImage:string,
     *     storeUrl:string,
     *     hasLocation:bool,
     *     locationLabel:string,
     *     categoryName:string,
     *     featuredLabel:?string,
     *     serviceTags:array<int, string>,
     *     ratingScore:float,
     *     ratingCount:int
     * }
     */
    public static function data(Vendor $vendor, bool $hasLocation = false): array
    {
        $firstProduct = $vendor->products->first();
        $productImages = is_array($firstProduct?->images) ? array_filter($firstProduct->images) : [];
        $productImage = ! empty($productImages) ? asset($productImages[0]) : null;
        $bannerImage = $vendor->bannerSlides->first()?->image_path ? asset($vendor->bannerSlides->first()->image_path) : null;
        $logoImage = $vendor->logo ? asset($vendor->logo) : null;
        $coverImage = $bannerImage ?? $productImage ?? $logoImage ?? asset('assets/images/vendor-card-placeholder.svg');
        $avatarImage = $logoImage ?? $productImage ?? asset('assets/images/profile-placeholder.svg');
        $primaryBranch = $vendor->branches->first();
        $vendorLocation = $primaryBranch?->city ?: ($vendor->city ?: 'Local Area');
        $vendorState = $primaryBranch?->state ?: ($vendor->state ?? null);
        $locationLabel = $vendorState ? $vendorLocation.', '.$vendorState : $vendorLocation;
        $categoryName = $firstProduct?->category?->name
            ?: $firstProduct?->subcategory?->name
            ?: 'General';
        $featuredLabel = filled($firstProduct?->name)
            ? $firstProduct->name
            : (Str::limit(strip_tags((string) $vendor->description), 72) ?: null);
        $serviceTags = $vendor->products
            ->take(3)
            ->pluck('name')
            ->filter()
            ->values()
            ->all();
        $productCount = (int) ($vendor->products_count ?? $vendor->products->count());
        $ratingScore = min(5, round(4 + min($productCount, 40) * 0.025, 1));
        $ratingCount = max($productCount, 1);

        return [
            'coverImage' => $coverImage,
            'avatarImage' => $avatarImage,
            'storeUrl' => route('store.show', $vendor->slug),
            'hasLocation' => $hasLocation,
            'locationLabel' => $locationLabel,
            'categoryName' => $categoryName,
            'featuredLabel' => $featuredLabel,
            'serviceTags' => $serviceTags,
            'ratingScore' => $ratingScore,
            'ratingCount' => $ratingCount,
        ];
    }
}
