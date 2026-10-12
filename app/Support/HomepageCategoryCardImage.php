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
        return self::resolve(
            CategoryHomepageImage::CONTEXT_VENDORS,
            HomepageCardCategory::idsForVendor($vendor),
            $entityCover
        );
    }

    public static function forServiceProvider(ServiceProvider $serviceProvider, string $entityCover): string
    {
        return self::resolve(
            CategoryHomepageImage::CONTEXT_SERVICES,
            HomepageCardCategory::idsForServiceProvider($serviceProvider),
            $entityCover
        );
    }

    public static function forConsultant(Consultant $consultant, string $entityCover): string
    {
        return self::resolve(
            CategoryHomepageImage::CONTEXT_CONSULTANTS,
            HomepageCardCategory::idsForConsultant($consultant),
            $entityCover
        );
    }

    public static function forOffer(Offer $offer, string $entityCover): string
    {
        return self::resolve(
            CategoryHomepageImage::CONTEXT_OFFERS,
            HomepageCardCategory::idsForOffer($offer),
            $entityCover
        );
    }

    public static function forUserAd(UserAd $ad, string $entityCover): string
    {
        return self::resolve(
            CategoryHomepageImage::CONTEXT_ADS,
            HomepageCardCategory::idsForAd($ad),
            $entityCover
        );
    }

    /**
     * @param  list<string>  $keywords
     */
    public static function forEducation(array $keywords, string $entityCover): string
    {
        return self::resolve(
            CategoryHomepageImage::CONTEXT_EDUCATION,
            HomepageCardCategory::idsFromKeywords($keywords),
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
