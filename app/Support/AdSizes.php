<?php

namespace App\Support;

use App\Models\AdSize;
use Illuminate\Contracts\Auth\Authenticatable;

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
    public static function visibleFor(?Authenticatable $user): array
    {
        if ($user && method_exists($user, 'isStaff') && $user->isStaff()) {
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
     * Per-day placement price: base + sum(selected modules) + highest paid category price.
     */
    public static function placementPricePerDay(array $size, array $selectedModules = [], array $selectedCategoryIds = []): float
    {
        if (! ($size['is_paid'] ?? false)) {
            return 0.0;
        }

        $base = self::basePricePerDay($size) ?? 0.0;

        $moduleTotal = collect($selectedModules)
            ->map(fn ($key) => (string) $key)
            ->unique()
            ->sum(fn (string $moduleKey) => (float) ($size['module_prices'][$moduleKey] ?? 0));

        $categoryTotal = collect($selectedCategoryIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->map(fn (int $categoryId) => (float) ($size['category_prices'][$categoryId] ?? 0))
            ->filter(fn (float $amount) => $amount > 0)
            ->max() ?? 0.0;

        return round($base + $moduleTotal + (float) $categoryTotal, 2);
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

    /**
     * @return list<string>
     */
    public static function sponsoredFillerDimensions(): array
    {
        return [
            '458x458', '458x229', '229x458', '229x229',
            '520x360', '520x300', '458x300', '360x360', '320x300',
        ];
    }

    public static function isSponsoredFillerDimension(int $width, int $height): bool
    {
        if ($width <= 0 || $height <= 0) {
            return false;
        }

        return in_array($width.'x'.$height, self::sponsoredFillerDimensions(), true);
    }

    public static function isSponsoredFillerSize(array $size): bool
    {
        if (! (bool) ($size['admin_only'] ?? false)) {
            return false;
        }

        return self::isSponsoredFillerDimension((int) ($size['w'] ?? 0), (int) ($size['h'] ?? 0));
    }

    /**
     * @return array{w:int, h:int}|null
     */
    public static function dimensionsForSizeType(string $sizeType, bool $includeInactive = true): ?array
    {
        $normalizedType = strtolower(str_replace([' ', '-'], '_', trim($sizeType)));
        $sizes = self::all($includeInactive);

        foreach ($sizes as $key => $size) {
            $normalizedKey = strtolower(str_replace([' ', '-'], '_', (string) $key));
            if ($normalizedKey === $normalizedType) {
                return [
                    'w' => (int) $size['w'],
                    'h' => (int) $size['h'],
                ];
            }
        }

        $adSize = AdSize::query()
            ->where('size_key', $sizeType)
            ->first(['width', 'height']);

        if ($adSize && $adSize->width > 0 && $adSize->height > 0) {
            return [
                'w' => (int) $adSize->width,
                'h' => (int) $adSize->height,
            ];
        }

        return null;
    }

    /**
     * Sponsored blank-slot sizes loaded from ad_sizes (exact DB width/height + labels).
     *
     * @return list<array{size_key:string, name:string, w:int, h:int}>
     */
    public static function sponsoredFillerSizesFromDatabase(bool $includeInactive = true): array
    {
        $allowedDimensions = collect(self::sponsoredFillerDimensions())->flip();

        return AdSize::query()
            ->when(! $includeInactive, fn ($query) => $query->where('is_active', true))
            ->where('admin_only', true)
            ->where('width', '>', 0)
            ->where('height', '>', 0)
            ->orderBy('name')
            ->get(['size_key', 'name', 'width', 'height'])
            ->filter(function (AdSize $size) use ($allowedDimensions) {
                return $allowedDimensions->has(((int) $size->width).'x'.((int) $size->height));
            })
            ->map(fn (AdSize $size) => [
                'size_key' => (string) $size->size_key,
                'name' => (string) $size->name,
                'w' => (int) $size->width,
                'h' => (int) $size->height,
            ])
            ->unique(fn (array $size) => $size['w'].'x'.$size['h'])
            ->values()
            ->all();
    }
}
