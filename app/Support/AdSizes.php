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
            'top_categories_ad_1' => ['name' => 'Top Categories Ad 1', 'ratio' => '879 / 118', 'w' => 879, 'h' => 118, 'admin_only' => true],
            'top_categories_ad_2' => ['name' => 'Top Categories Ad 2', 'ratio' => '296 / 132', 'w' => 296, 'h' => 132, 'admin_only' => true],
            'sponsored_listings_ad' => ['name' => 'Sponsored Listings Ad', 'ratio' => '296 / 624', 'w' => 296, 'h' => 624, 'admin_only' => true],
            'below_sponsored_ad' => ['name' => 'Below Sponsored Listings Ad', 'ratio' => '1232 / 145', 'w' => 1232, 'h' => 145, 'admin_only' => true],
            'ecommerce_ad' => ['name' => 'E-Commerce Ad', 'ratio' => '289 / 186', 'w' => 289, 'h' => 186, 'admin_only' => true],
            'offer_discount_ad_1' => ['name' => 'Offer & Discount Ad 1', 'ratio' => '884 / 160', 'w' => 884, 'h' => 160, 'admin_only' => true],
            'offer_discount_ad_2' => ['name' => 'Offer & Discount Ad 2', 'ratio' => '277 / 340', 'w' => 277, 'h' => 340, 'admin_only' => true],
            'explore_products_ad' => ['name' => 'Explore Products Near You Ad', 'ratio' => '1191 / 138', 'w' => 1191, 'h' => 138, 'admin_only' => true],
            'top_vendors_ad_1' => ['name' => 'Top Vendors Ad 1', 'ratio' => '1191 / 77', 'w' => 1191, 'h' => 77, 'admin_only' => true],
            'top_vendors_ad_2' => ['name' => 'Top Vendors Ad 2', 'ratio' => '301 / 247', 'w' => 301, 'h' => 247, 'admin_only' => true],
            'popular_greenwood_ad' => ['name' => 'Popular Properties Near Greenwood Ad', 'ratio' => '382 / 749', 'w' => 382, 'h' => 749, 'admin_only' => true],
            'popular_properties_ad' => ['name' => 'Popular Properties Ad', 'ratio' => '462 / 413', 'w' => 462, 'h' => 413, 'admin_only' => true],
            'below_popular_ad' => ['name' => 'Below Popular Properties Ad', 'ratio' => '1232 / 145', 'w' => 1232, 'h' => 145, 'admin_only' => true],
            'builders_developers_ad' => ['name' => 'Builders & Developers Ad', 'ratio' => '292 / 271', 'w' => 292, 'h' => 271, 'admin_only' => true],
            'below_builders_ad' => ['name' => 'Below Builders & Developers Ad', 'ratio' => '1232 / 145', 'w' => 1232, 'h' => 145, 'admin_only' => true],
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
