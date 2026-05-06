<?php

namespace App\Support;

use App\Models\AdSize;
use App\Models\User;

final class AdSizes
{
    /**
     * @return array<string, array{name:string, ratio:string, w:int, h:int, admin_only:bool, is_paid:bool, amount:float}>
     */
    public static function all(): array
    {
        $sizes = [];

        $adSizes = AdSize::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        foreach ($adSizes as $adSize) {
            if ($adSize->height <= 0 || $adSize->width <= 0) {
                continue;
            }

            $sizes[$adSize->size_key] = [
                'name' => $adSize->name,
                'ratio' => $adSize->width.' / '.$adSize->height,
                'w' => (int) $adSize->width,
                'h' => (int) $adSize->height,
                'admin_only' => (bool) $adSize->admin_only,
                'is_paid' => (bool) $adSize->is_paid,
                'amount' => (float) ($adSize->amount ?? 0),
            ];
        }

        return $sizes;
    }


    /**
     * @return array<string, array{name:string, ratio:string, w:int, h:int, admin_only:bool, is_paid:bool, amount:float}>
     */
    public static function visibleFor(?User $user): array
    {
        $isAdmin = (bool) ($user?->isAdmin());

        return array_filter(
            self::all(),
            fn (array $size) => (bool) ($size['admin_only'] ?? false) === $isAdmin
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
