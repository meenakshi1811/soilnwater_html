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
     * @return Collection<int, Category>
     */
    public static function offerCategoryTree(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->whereJsonContains('modules', 'offers')
            ->with(['children' => fn ($query) => $query->orderBy('name')->select(['id', 'name', 'parent_id', 'offer_price', 'modules'])])
            ->orderBy('name')
            ->get(['id', 'name', 'parent_id', 'offer_price', 'modules']);
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
        $parentIds = Category::query()
            ->whereNull('parent_id')
            ->whereJsonContains('modules', 'offers')
            ->pluck('id');

        if ($parentIds->isEmpty()) {
            return 0;
        }

        return Category::query()
            ->where(function ($query) use ($parentIds): void {
                $query->where(function ($parentQuery): void {
                    $parentQuery->whereNull('parent_id')->whereJsonContains('modules', 'offers');
                })->orWhereIn('parent_id', $parentIds);
            })
            ->update(['offer_price' => $normalized]);
    }
}
