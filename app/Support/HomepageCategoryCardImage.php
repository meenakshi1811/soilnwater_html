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

    /** @var list<array{image: string, name: string, modules: list<string>}>|null */
    private static ?array $educationImages = null;

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
     * @param  array{category_id: ?int, subcategory_id: ?int, child_category_id: ?int}  $ids
     */
    public static function resolve(string $context, array $ids, string $entityCover): string
    {
        $configured = self::configuredImage($context, $ids);

        return $configured !== '' ? $configured : $entityCover;
    }

    /**
     * Admin category card image only; empty when none is configured for this context.
     *
     * @param  array{category_id: ?int, subcategory_id: ?int, child_category_id: ?int}  $ids
     */
    public static function configuredImage(string $context, array $ids): string
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

        return '';
    }

    /**
     * The Education & knowledge images (Admin → Category Card Images), each with its category's
     * name and modules (Admin → Categories: educators, schools, institutes).
     *
     * @return list<array{image: string, name: string, modules: list<string>}>
     */
    public static function educationImages(): array
    {
        if (self::$educationImages === null) {
            self::$educationImages = [];

            $rows = CategoryHomepageImage::query()
                ->where('context', CategoryHomepageImage::CONTEXT_EDUCATION)
                ->with('category:id,name,modules')
                ->get(['id', 'category_id', 'image_path']);

            foreach ($rows as $row) {
                $image = self::formatPath((string) $row->image_path);
                if ($row->category === null || $image === '') {
                    continue;
                }

                self::$educationImages[] = [
                    'image' => $image,
                    'name' => (string) $row->category->name,
                    'modules' => array_values((array) ($row->category->modules ?? [])),
                ];
            }
        }

        return self::$educationImages;
    }

    /**
     * The Education & knowledge images on the categories of one module.
     *
     * @return list<array{image: string, name: string, modules: list<string>}>
     */
    public static function educationImagesForModule(string $module): array
    {
        return array_values(array_filter(
            self::educationImages(),
            fn (array $row): bool => in_array($module, $row['modules'], true)
        ));
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
