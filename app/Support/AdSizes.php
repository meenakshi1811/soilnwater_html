<?php

namespace App\Support;

use App\Models\AdSize;
use App\Models\User;

final class AdSizes
{
    /**
     * @return array<string, array{name:string, ratio:string, w:int, h:int, admin_only:bool, is_paid:bool, amount:float, category_prices:array<int,float>}>
     */
    public static function all(bool $includeInactive = false): array
    {
        $sizes = [];

        $adSizes = AdSize::query()
            ->with('categoryPrices:id,ad_size_id,category_id,amount', 'modulePrices:id,ad_size_id,module_key,amount')
            ->when(! $includeInactive, fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get();

        foreach ($adSizes as $adSize) {
            if ($adSize->height <= 0 || $adSize->width <= 0) {
                continue;
            }

            $categoryPrices = $adSize->categoryPrices
                ->mapWithKeys(fn ($price) => [(int) $price->category_id => (float) $price->amount])
                ->all();

            $modulePrices = $adSize->modulePrices->mapWithKeys(fn ($price) => [(string) $price->module_key => (float) $price->amount])->all();

            $storedAmount = $adSize->amount !== null ? (float) $adSize->amount : null;
            $fallbackAmount = $categoryPrices !== []
                ? min($categoryPrices)
                : ($modulePrices !== [] ? min($modulePrices) : 0.0);

            $sizes[$adSize->size_key] = [
                'name' => $adSize->name,
                'ratio' => $adSize->width.' / '.$adSize->height,
                'w' => (int) $adSize->width,
                'h' => (int) $adSize->height,
                'admin_only' => (bool) $adSize->admin_only,
                'is_paid' => (bool) $adSize->is_paid,
                'module_prices' => $modulePrices,
                'amount' => $storedAmount !== null && $storedAmount > 0 ? $storedAmount : (float) $fallbackAmount,
                'category_prices' => $categoryPrices,
                'is_active' => (bool) $adSize->is_active,
            ];
        }

        return $sizes;
    }


    /**
     * @return array<string, array{name:string, ratio:string, w:int, h:int, admin_only:bool, is_paid:bool, amount:float, category_prices:array<int,float>}>
     */
    public static function visibleFor(?User $user): array
    {
        if ((bool) ($user?->isStaff())) {
            return self::all(true);
        }

        return array_filter(
            self::all(),
            fn (array $size) => ! ((bool) ($size['admin_only'] ?? false))
        );
    }

    public static function exists(string $sizeType, bool $includeInactive = false): bool
    {
        return array_key_exists($sizeType, self::all($includeInactive));
    }

    public static function label(string $sizeType): string
    {
        return self::all()[$sizeType]['name'] ?? $sizeType;
    }

    /**
     * Starting base price per day for a size (lowest category / module / flat amount).
     */
    public static function basePricePerDay(array $size): ?float
    {
        if (! (bool) ($size['is_paid'] ?? false)) {
            return null;
        }

        $amount = (float) ($size['amount'] ?? 0);

        return $amount > 0 ? round($amount, 2) : null;
    }

    /**
     * Highest possible base price per day for a size (max category + all modules).
     */
    public static function maxPricePerDay(array $size): ?float
    {
        if (! (bool) ($size['is_paid'] ?? false)) {
            return null;
        }

        $categoryPrices = $size['category_prices'] ?? [];
        $modulePrices = $size['module_prices'] ?? [];
        $maxCategory = $categoryPrices !== [] ? (float) max($categoryPrices) : 0.0;
        $moduleTotal = (float) array_sum($modulePrices);
        $total = $maxCategory + $moduleTotal;

        return $total > 0 ? round($total, 2) : null;
    }
}
