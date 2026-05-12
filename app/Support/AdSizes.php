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
            ->with('categoryPrices:id,ad_size_id,category_id,amount')
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

            $sizes[$adSize->size_key] = [
                'name' => $adSize->name,
                'ratio' => $adSize->width.' / '.$adSize->height,
                'w' => (int) $adSize->width,
                'h' => (int) $adSize->height,
                'admin_only' => (bool) $adSize->admin_only,
                'is_paid' => (bool) $adSize->is_paid,
                'amount' => $categoryPrices !== [] ? min($categoryPrices) : (float) ($adSize->amount ?? 0),
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

    public static function exists(string $sizeType): bool
    {
        return array_key_exists($sizeType, self::all());
    }

    public static function label(string $sizeType): string
    {
        return self::all()[$sizeType]['name'] ?? $sizeType;
    }
}
