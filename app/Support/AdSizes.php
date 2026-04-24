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
            'attached_ads_side' => ['name' => 'Attached Ads Section (Side)', 'ratio' => '5 / 7', 'w' => 500, 'h' => 700, 'admin_only' => true],
            'ecommerce_ads_section' => ['name' => 'E-Commerce Ads Section', 'ratio' => '4 / 3', 'w' => 800, 'h' => 600, 'admin_only' => true],
            'offer_discount_top_ads' => ['name' => 'Offer & Discount (Top Ads)', 'ratio' => '16 / 5', 'w' => 1600, 'h' => 500, 'admin_only' => true],
            'offer_discount_side_ads' => ['name' => 'Offer & Discount (Side Ads)', 'ratio' => '5 / 7', 'w' => 500, 'h' => 700, 'admin_only' => true],
            'explore_products_ads' => ['name' => 'Explore Products Near You Ads', 'ratio' => '4 / 1', 'w' => 1200, 'h' => 300, 'admin_only' => true],
            'top_vendors_top_ads' => ['name' => 'Top Vendors (Top Ads)', 'ratio' => '4 / 1', 'w' => 1200, 'h' => 300, 'admin_only' => true],
            'top_vendors_side_ads' => ['name' => 'Top Vendors (Side Ads)', 'ratio' => '5 / 7', 'w' => 500, 'h' => 700, 'admin_only' => true],
            'pp_greenwood_side_ads' => ['name' => 'Popular Properties Near Greenwood (Side Ads)', 'ratio' => '5 / 7', 'w' => 500, 'h' => 700, 'admin_only' => true],
            'popular_props_side_ads' => ['name' => 'Popular Properties (Side Ads)', 'ratio' => '5 / 7', 'w' => 500, 'h' => 700, 'admin_only' => true],
            'sponsored_placement_ads' => ['name' => 'Sponsored Placement Ads', 'ratio' => '16 / 5', 'w' => 1600, 'h' => 500, 'admin_only' => true],
            'builders_side_ads' => ['name' => 'Builders & Developers (Side Ads)', 'ratio' => '5 / 7', 'w' => 500, 'h' => 700, 'admin_only' => true],
            'builders_sponsored_below_ads' => ['name' => 'Builders Sponsored Placement (Below)', 'ratio' => '16 / 5', 'w' => 1600, 'h' => 500, 'admin_only' => true],
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
