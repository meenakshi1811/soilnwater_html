<?php

namespace App\Support;

use App\Models\Category;
use App\Models\CategoryHomepageImage;
use App\Models\ConsultantService;
use App\Models\Offer;
use App\Models\ServiceProviderService;
use App\Models\UserAd;
use App\Models\Vendor;
use App\Models\VendorProduct;

final class CategoryHomepagePostingCounts
{
    /** @var array<string, array<int, int>|null> */
    private static array $cache = [];

    /**
     * @var array<string, list<array{model: class-string, owner: string, columns: list<string>}>>
     */
    private const CONTEXT_CATEGORY_SOURCES = [
        CategoryHomepageImage::CONTEXT_ADS => [
            ['model' => UserAd::class, 'owner' => 'id', 'columns' => ['category_id', 'subcategory_id']],
        ],
        CategoryHomepageImage::CONTEXT_OFFERS => [
            ['model' => Offer::class, 'owner' => 'id', 'columns' => ['category_id', 'subcategory_id']],
        ],
        CategoryHomepageImage::CONTEXT_VENDORS => [
            ['model' => VendorProduct::class, 'owner' => 'vendor_id', 'columns' => ['category_id', 'subcategory_id', 'child_category_id']],
            ['model' => Vendor::class, 'owner' => 'id', 'columns' => ['business_category_id', 'business_subcategory_id']],
        ],
        CategoryHomepageImage::CONTEXT_SERVICES => [
            ['model' => ServiceProviderService::class, 'owner' => 'service_provider_id', 'columns' => ['category_id', 'subcategory_id']],
        ],
        CategoryHomepageImage::CONTEXT_CONSULTANTS => [
            ['model' => ConsultantService::class, 'owner' => 'consultant_id', 'columns' => ['category_id', 'subcategory_id']],
        ],
    ];

    /**
     * @return array<int, int>|null category id => posting count; null when the section has no postings (e.g. education)
     */
    public static function forContext(string $context): ?array
    {
        if (array_key_exists($context, self::$cache)) {
            return self::$cache[$context];
        }

        $sources = self::CONTEXT_CATEGORY_SOURCES[$context] ?? null;
        if ($sources === null) {
            return self::$cache[$context] = null;
        }

        /** @var array<int, array<int, true>> $owners */
        $owners = [];
        foreach ($sources as $source) {
            foreach ($source['columns'] as $column) {
                $pairs = $source['model']::query()->toBase()
                    ->whereNotNull($column)
                    ->distinct()
                    ->get([$column.' as category_id', $source['owner'].' as owner_id']);

                foreach ($pairs as $pair) {
                    $owners[(int) $pair->category_id][(int) $pair->owner_id] = true;
                }
            }
        }

        $parents = self::parentMap(array_keys($owners));
        $totals = [];
        foreach ($owners as $categoryId => $ownerIds) {
            for ($id = $categoryId, $level = 0; $id !== null && $level < 3; $id = $parents[$id] ?? null, $level++) {
                $totals[$id] = ($totals[$id] ?? []) + $ownerIds;
            }
        }

        return self::$cache[$context] = array_map('count', $totals);
    }

    /**
     * @param  list<int>  $ids
     * @return array<int, int|null>
     */
    private static function parentMap(array $ids): array
    {
        $map = [];
        while ($ids !== []) {
            $next = [];
            foreach (Category::query()->whereIn('id', $ids)->pluck('parent_id', 'id') as $id => $parentId) {
                $map[(int) $id] = $parentId === null ? null : (int) $parentId;
                if ($parentId !== null) {
                    $next[] = (int) $parentId;
                }
            }
            $ids = array_values(array_unique(array_diff($next, array_keys($map))));
        }

        return $map;
    }
}
