<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Consultant;
use App\Models\Offer;
use App\Models\ServiceProvider;
use App\Models\ServiceProviderService;
use App\Models\UserAd;
use App\Models\Vendor;
use App\Models\VendorProduct;

final class HomepageCardCategory
{
    /**
     * @return array{category_id: ?int, subcategory_id: ?int, child_category_id: ?int}
     */
    public static function idsFromKeywords(array $keywords): array
    {
        return self::hierarchyIds(self::findCategoryByKeywords($keywords));
    }

    public static function displayNameForServiceProvider(ServiceProvider $serviceProvider): string
    {
        return self::displayNameForService($serviceProvider->services->first());
    }

    public static function displayNameForVendor(Vendor $vendor): string
    {
        $name = self::displayNameForProduct($vendor->products->first());

        // No categorised product: fall back to the business subcategory/category set in Admin → Vendors
        if ($name === '') {
            return $vendor->businessSubcategory?->name ?: $vendor->businessCategory?->name ?: '';
        }

        return $name;
    }

    public static function displayNameForConsultant(Consultant $consultant): string
    {
        return self::displayNameForConsultantService($consultant->services->first());
    }

    public static function displayNameForOffer(Offer $offer): string
    {
        return $offer->subcategory?->name
            ?: $offer->category?->name
            ?: 'Local offer';
    }

    public static function displayNameForAd(UserAd $ad): string
    {
        return $ad->subcategory?->name
            ?: $ad->category?->name
            ?: 'Featured ad';
    }

    /**
     * @return array{category_id: ?int, subcategory_id: ?int, child_category_id: ?int}
     */
    public static function idsForServiceProvider(ServiceProvider $serviceProvider): array
    {
        return self::idsFromService($serviceProvider->services->first());
    }

    /**
     * @return array{category_id: ?int, subcategory_id: ?int, child_category_id: ?int}
     */
    public static function idsForVendor(Vendor $vendor): array
    {
        $ids = self::idsFromProduct($vendor->products->first());

        // No categorised product: use the business subcategory/category set in Admin → Vendors
        if (array_filter($ids) === []) {
            return self::hierarchyIds($vendor->businessSubcategory ?? $vendor->businessCategory);
        }

        return $ids;
    }

    /**
     * @return array{category_id: ?int, subcategory_id: ?int, child_category_id: ?int}
     */
    public static function idsForConsultant(Consultant $consultant): array
    {
        return self::idsFromConsultantService($consultant->services->first());
    }

    /**
     * @return array{category_id: ?int, subcategory_id: ?int, child_category_id: ?int}
     */
    public static function idsForOffer(Offer $offer): array
    {
        return [
            'category_id' => $offer->category_id,
            'subcategory_id' => $offer->subcategory_id,
            'child_category_id' => null,
        ];
    }

    /**
     * @return array{category_id: ?int, subcategory_id: ?int, child_category_id: ?int}
     */
    public static function idsForAd(UserAd $ad): array
    {
        return [
            'category_id' => $ad->category_id,
            'subcategory_id' => $ad->subcategory_id,
            'child_category_id' => null,
        ];
    }

    /**
     * @return array{category_id: ?int, subcategory_id: ?int, child_category_id: ?int}
     */
    public static function hierarchyIds(?Category $category): array
    {
        if (! $category instanceof Category) {
            return [
                'category_id' => null,
                'subcategory_id' => null,
                'child_category_id' => null,
            ];
        }

        $category->loadMissing('parent.parent');

        if ($category->parent_id === null) {
            return [
                'category_id' => $category->id,
                'subcategory_id' => null,
                'child_category_id' => null,
            ];
        }

        $parent = $category->parent;
        if ($parent && $parent->parent_id === null) {
            return [
                'category_id' => $parent->id,
                'subcategory_id' => $category->id,
                'child_category_id' => null,
            ];
        }

        $grandparent = $parent?->parent;

        return [
            'category_id' => $grandparent?->id,
            'subcategory_id' => $parent?->id,
            'child_category_id' => $category->id,
        ];
    }

    /**
     * @param  list<string>  $keywords
     */
    private static function findCategoryByKeywords(array $keywords): ?Category
    {
        foreach ($keywords as $keyword) {
            $keyword = trim($keyword);
            if ($keyword === '') {
                continue;
            }

            $match = Category::query()
                ->where('name', 'like', '%'.$keyword.'%')
                ->orderByRaw('CASE WHEN parent_id IS NULL THEN 1 ELSE 0 END')
                ->orderBy('name')
                ->first();

            if ($match instanceof Category) {
                return $match;
            }
        }

        return null;
    }

    /**
     * Empty when no category is known: the homepage card then shows no category label.
     */
    private static function displayNameForService(?ServiceProviderService $service): string
    {
        if (! $service instanceof ServiceProviderService) {
            return '';
        }

        return $service->subcategoryModel?->name
            ?: $service->categoryModel?->name
            ?: (filled($service->category) ? (string) $service->category : '');
    }

    private static function displayNameForConsultantService(?object $service): string
    {
        if ($service === null) {
            return '';
        }

        return $service->subcategoryModel?->name
            ?: $service->categoryModel?->name
            ?: (filled($service->category ?? null) ? (string) $service->category : '');
    }

    private static function displayNameForProduct(?VendorProduct $product): string
    {
        if (! $product instanceof VendorProduct) {
            return '';
        }

        return $product->subcategory?->name
            ?: $product->childCategory?->name
            ?: $product->category?->name
            ?: '';
    }

    /**
     * @return array{category_id: ?int, subcategory_id: ?int, child_category_id: ?int}
     */
    private static function idsFromService(?ServiceProviderService $service): array
    {
        if (! $service instanceof ServiceProviderService) {
            return [
                'category_id' => null,
                'subcategory_id' => null,
                'child_category_id' => null,
            ];
        }

        return [
            'category_id' => $service->category_id,
            'subcategory_id' => $service->subcategory_id,
            'child_category_id' => null,
        ];
    }

    /**
     * @return array{category_id: ?int, subcategory_id: ?int, child_category_id: ?int}
     */
    private static function idsFromConsultantService(?object $service): array
    {
        if ($service === null) {
            return [
                'category_id' => null,
                'subcategory_id' => null,
                'child_category_id' => null,
            ];
        }

        return [
            'category_id' => $service->category_id ?? null,
            'subcategory_id' => $service->subcategory_id ?? null,
            'child_category_id' => null,
        ];
    }

    /**
     * @return array{category_id: ?int, subcategory_id: ?int, child_category_id: ?int}
     */
    private static function idsFromProduct(?VendorProduct $product): array
    {
        if (! $product instanceof VendorProduct) {
            return [
                'category_id' => null,
                'subcategory_id' => null,
                'child_category_id' => null,
            ];
        }

        return [
            'category_id' => $product->category_id,
            'subcategory_id' => $product->subcategory_id,
            'child_category_id' => $product->child_category_id,
        ];
    }
}
