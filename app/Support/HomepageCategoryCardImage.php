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

    /** @var list<array{names: list<string>, parent_names: list<string>, image: string}>|null */
    private static ?array $educationRows = null;

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
        // Education cards have no category of their own: first an Education & knowledge image
        // whose category is named like the card's keywords
        $image = self::educationImageByKeywords($keywords);
        if ($image !== '') {
            return $image;
        }

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
     * The Education & knowledge image whose category name contains one of the keywords (tried in
     * order), else whose parent category's name does.
     *
     * @param  list<string>  $keywords
     */
    private static function educationImageByKeywords(array $keywords): string
    {
        if (self::$educationRows === null) {
            self::$educationRows = CategoryHomepageImage::query()
                ->where('context', CategoryHomepageImage::CONTEXT_EDUCATION)
                ->with(['category:id,name,parent_id', 'category.parent:id,name,parent_id', 'category.parent.parent:id,name'])
                ->get()
                ->filter(fn (CategoryHomepageImage $row): bool => $row->category !== null && self::formatPath((string) $row->image_path) !== '')
                ->map(fn (CategoryHomepageImage $row): array => [
                    'names' => [mb_strtolower($row->category->name)],
                    'parent_names' => array_values(array_filter([
                        mb_strtolower((string) $row->category->parent?->name),
                        mb_strtolower((string) $row->category->parent?->parent?->name),
                    ])),
                    'image' => self::formatPath((string) $row->image_path),
                ])
                ->values()
                ->all();
        }

        foreach ($keywords as $keyword) {
            $keyword = mb_strtolower(trim($keyword));
            if ($keyword === '') {
                continue;
            }

            foreach (['names', 'parent_names'] as $field) {
                foreach (self::$educationRows as $row) {
                    foreach ($row[$field] as $name) {
                        if (str_contains($name, $keyword)) {
                            return $row['image'];
                        }
                    }
                }
            }
        }

        return '';
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
