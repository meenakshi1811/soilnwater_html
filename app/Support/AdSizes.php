<?php

namespace App\Support;

final class AdSizes
{
    /**
     * @return array<string, array{name:string, ratio:string, w:int, h:int, admin_only:bool}>
     */
    public static function all(): array
    {
        return [
            'square' => ['name' => 'Square', 'ratio' => '1 / 1', 'w' => 640, 'h' => 640, 'admin_only' => false],
            'vertical_rectangle' => ['name' => 'Vertical Rectangle', 'ratio' => '2 / 3', 'w' => 600, 'h' => 900, 'admin_only' => false],
            'horizontal' => ['name' => 'Horizontal', 'ratio' => '3 / 2', 'w' => 900, 'h' => 600, 'admin_only' => false],
            'square_large' => ['name' => 'Square Large', 'ratio' => '1 / 1', 'w' => 900, 'h' => 900, 'admin_only' => false],
            'banner' => ['name' => 'Banner', 'ratio' => '4 / 1', 'w' => 1200, 'h' => 300, 'admin_only' => false],
            'full_page' => ['name' => 'Full page', 'ratio' => '3 / 4', 'w' => 900, 'h' => 1200, 'admin_only' => false],
            'home_top_slider' => ['name' => 'Home Top Slider', 'ratio' => '4 / 1', 'w' => 1200, 'h' => 300, 'admin_only' => true],
            'home_side_slider' => ['name' => 'Home Side Slider', 'ratio' => '5 / 7', 'w' => 500, 'h' => 700, 'admin_only' => true],
            'home_recent_card' => ['name' => 'Home Recent Card', 'ratio' => '4 / 3', 'w' => 800, 'h' => 600, 'admin_only' => true],
            'home_offer_poster' => ['name' => 'Home Offer Poster', 'ratio' => '64 / 90', 'w' => 768, 'h' => 1080, 'admin_only' => true],
            'home_wide_slider' => ['name' => 'Home Wide Slider', 'ratio' => '16 / 5', 'w' => 1600, 'h' => 500, 'admin_only' => true],
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
