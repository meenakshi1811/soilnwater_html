<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Collection;

class OfferPriceService
{
    public static function formatAmount(float|string|null $amount): string
    {
        return '₹'.number_format((float) $amount, 2);
    }

    public static function resolveAppliedPrice(int $categoryId, int $subcategoryId = 0): float
    {
        $categoryPrice = (float) (Category::query()->where('id', $categoryId)->value('offer_price') ?? 0);

        if ($subcategoryId <= 0) {
            return $categoryPrice;
        }

        $subcategoryPrice = (float) (Category::query()
            ->where('id', $subcategoryId)
            ->where('parent_id', $categoryId)
            ->value('offer_price') ?? 0);

        return $subcategoryPrice > 0 ? $subcategoryPrice : $categoryPrice;
    }

    /**
     * @return Collection<int, int>
     */
    public static function offerPricingCategoryIds(): Collection
    {
        $rootIds = Category::query()
            ->whereNull('parent_id')
            ->whereJsonContains('modules', 'offers')
            ->pluck('id');

        if ($rootIds->isEmpty()) {
            return collect();
        }

        $ids = collect($rootIds);
        $parentIds = $rootIds;

        while ($parentIds->isNotEmpty()) {
            $children = Category::query()
                ->whereIn('parent_id', $parentIds)
                ->pluck('id');

            $ids = $ids->merge($children);
            $parentIds = $children;
        }

        return $ids->unique()->values();
    }

    /**
     * @return Collection<int, Category>
     */
    public static function offerCategoryTree(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->whereJsonContains('modules', 'offers')
            ->with(['childrenRecursive' => fn ($query) => $query
                ->orderBy('name')
                ->select(['id', 'name', 'parent_id', 'offer_price', 'modules'])])
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id', 'offer_price', 'modules'])
            ->each(function (Category $category): void {
                $category->setRelation('children', $category->childrenRecursive);
            });
    }

    public static function countDescendants(Category $category): int
    {
        $count = 0;

        foreach ($category->children as $child) {
            $count++;
            $count += self::countDescendants($child);
        }

        return $count;
    }

    public static function isOfferPricingCategory(Category $category): bool
    {
        if ($category->parent_id) {
            $parent = $category->relationLoaded('parent')
                ? $category->parent
                : Category::query()->find($category->parent_id);

            return (bool) $parent && in_array('offers', $parent->modules ?? [], true);
        }

        return in_array('offers', $category->modules ?? [], true);
    }

    public static function applyToAll(float $amount): int
    {
        $normalized = max(0, round($amount, 2));
        $categoryIds = self::offerPricingCategoryIds();

        if ($categoryIds->isEmpty()) {
            return 0;
        }

        return Category::query()
            ->whereIn('id', $categoryIds)
            ->update(['offer_price' => $normalized]);
    }
}
