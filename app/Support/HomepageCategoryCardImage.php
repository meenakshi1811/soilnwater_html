<?php

namespace App\Support;

use App\Models\CategoryHomepageImage;
use App\Models\Consultant;
use App\Models\Offer;
use App\Models\ServiceProvider;
use App\Models\UserAd;
use App\Models\Vendor;

final class HomepageCategoryCardImage
{
    /** @var array<string, string>|null */
    private static ?array $cache = null;

    public static function warmCache(): void
    {
        if (self::$cache !== null) {
            return;
        }

        self::$cache = [];
        foreach (CategoryHomepageImage::query()->get(['category_id', 'context', 'image_path']) as $row) {
            $url = self::formatPath($row->image_path);
            if ($url !== '') {
                self::$cache[$row->context.':'.$row->category_id] = $url;
            }
        }
    }

    public static function forVendor(Vendor $vendor, string $entityCover): string
    {
        $product = $vendor->products->first();

        return self::resolve(
            CategoryHomepageImage::CONTEXT_VENDORS,
            self::idsFromProductLike($product),
            $entityCover
        );
    }

    public static function forServiceProvider(ServiceProvider $serviceProvider, string $entityCover): string
    {
        $service = $serviceProvider->services->first();

        return self::resolve(
            CategoryHomepageImage::CONTEXT_SERVICES,
            [
                'category_id' => $service?->category_id,
                'subcategory_id' => $service?->subcategory_id,
                'child_category_id' => null,
            ],
            $entityCover
        );
    }

    public static function forConsultant(Consultant $consultant, string $entityCover): string
    {
        $service = $consultant->services->first();

        return self::resolve(
            CategoryHomepageImage::CONTEXT_CONSULTANTS,
            [
                'category_id' => $service?->category_id,
                'subcategory_id' => $service?->subcategory_id,
                'child_category_id' => null,
            ],
            $entityCover
        );
    }

    public static function forOffer(Offer $offer, string $entityCover): string
    {
        return self::resolve(
            CategoryHomepageImage::CONTEXT_OFFERS,
            [
                'category_id' => $offer->category_id,
                'subcategory_id' => $offer->subcategory_id,
                'child_category_id' => null,
            ],
            $entityCover
        );
    }

    public static function forUserAd(UserAd $ad, string $entityCover): string
    {
        return self::resolve(
            CategoryHomepageImage::CONTEXT_ADS,
            [
                'category_id' => $ad->category_id,
                'subcategory_id' => $ad->subcategory_id,
                'child_category_id' => null,
            ],
            $entityCover
        );
    }

    /**
     * @param  array{category_id: ?int, subcategory_id: ?int, child_category_id: ?int}  $ids
     */
    public static function resolve(string $context, array $ids, string $entityCover): string
    {
        self::warmCache();

        foreach ([$ids['child_category_id'] ?? null, $ids['subcategory_id'] ?? null, $ids['category_id'] ?? null] as $categoryId) {
            if (! $categoryId) {
                continue;
            }

            $key = $context.':'.$categoryId;
            if (isset(self::$cache[$key])) {
                return self::$cache[$key];
            }
        }

        return $entityCover;
    }

    /**
     * @return array{category_id: ?int, subcategory_id: ?int, child_category_id: ?int}
     */
    private static function idsFromProductLike(?object $product): array
    {
        if ($product === null) {
            return [
                'category_id' => null,
                'subcategory_id' => null,
                'child_category_id' => null,
            ];
        }

        return [
            'category_id' => $product->category_id ?? null,
            'subcategory_id' => $product->subcategory_id ?? null,
            'child_category_id' => $product->child_category_id ?? null,
        ];
    }

    private static function formatPath(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset($path);
    }
}
