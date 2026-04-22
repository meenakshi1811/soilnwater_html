<?php

namespace App\Support;

final class AdSizes
{
    /**
     * @return array<string, array{name:string, ratio:string, w:int, h:int}>
     */
    public static function all(): array
    {
        return [
            'square' => ['name' => 'Square', 'ratio' => '1 / 1', 'w' => 600, 'h' => 600],
            'vertical_rectangle' => ['name' => 'Vertical Rectangle', 'ratio' => '2 / 3', 'w' => 600, 'h' => 900],
            'horizontal' => ['name' => 'Horizontal', 'ratio' => '3 / 2', 'w' => 900, 'h' => 600],
            'square_large' => ['name' => 'Square Large', 'ratio' => '1 / 1', 'w' => 900, 'h' => 900],
            'banner' => ['name' => 'Banner', 'ratio' => '4 / 1', 'w' => 1200, 'h' => 300],
            'full_page' => ['name' => 'Full page', 'ratio' => '3 / 4', 'w' => 900, 'h' => 1200],
        ];
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

