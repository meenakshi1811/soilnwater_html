<?php

namespace Database\Seeders;

use App\Models\AdSize;
use Illuminate\Database\Seeder;

class AdSizeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->userPlacementSizes() as $sizeKey => $size) {
            $this->upsertSize($sizeKey, $size['name'], $size['w'], $size['h'], false);
        }

        foreach ($this->adminPlacementSizes() as $sizeKey => $size) {
            $this->upsertSize($sizeKey, $size['name'], $size['w'], $size['h'], true);
        }
    }

    private function upsertSize(string $sizeKey, string $name, int $width, int $height, bool $adminOnly): void
    {
        AdSize::query()->updateOrCreate(
            ['size_key' => $sizeKey],
            [
                'name' => $name,
                'width' => $width,
                'height' => $height,
                'admin_only' => $adminOnly,
                'is_active' => true,
            ]
        );
    }

    /**
     * @return array<string, array{name:string,w:int,h:int}>
     */
    private function userPlacementSizes(): array
    {
        return [
            'square' => ['name' => 'Square', 'w' => 640, 'h' => 640],
            'vertical_rectangle' => ['name' => 'Vertical Rectangle', 'w' => 600, 'h' => 900],
            'horizontal' => ['name' => 'Horizontal', 'w' => 900, 'h' => 600],
            'square_large' => ['name' => 'Square Large', 'w' => 900, 'h' => 900],
            'banner' => ['name' => 'Banner', 'w' => 1200, 'h' => 300],
            'full_page' => ['name' => 'Full page', 'w' => 900, 'h' => 1200],
        ];
    }

    /**
     * @return array<string, array{name:string,w:int,h:int}>
     */
    private function adminPlacementSizes(): array
    {
        return [
            'top_categories_ad_1' => ['name' => 'Top Categories Ad 1', 'w' => 879, 'h' => 118],
            'top_categories_ad_2' => ['name' => 'Top Categories Ad 2', 'w' => 296, 'h' => 292],
            'sponsored_listings_ad' => ['name' => 'Sponsored Listings Ad', 'w' => 296, 'h' => 624],
            'below_sponsored_ad' => ['name' => 'Below Sponsored Listings Ad', 'w' => 1232, 'h' => 145],
            'ecommerce_ad' => ['name' => 'E-Commerce Ad', 'w' => 289, 'h' => 186],
            'offer_discount_ad_1' => ['name' => 'Offer & Discount Ad 1', 'w' => 884, 'h' => 160],
            'offer_discount_ad_2' => ['name' => 'Offer & Discount Ad 2', 'w' => 277, 'h' => 340],
            'explore_products_ad' => ['name' => 'Explore Products Near You Ad', 'w' => 1191, 'h' => 138],
            'top_vendors_ad_1' => ['name' => 'Top Vendors Ad 1', 'w' => 1191, 'h' => 77],
            'top_vendors_ad_2' => ['name' => 'Top Vendors Ad 2', 'w' => 301, 'h' => 247],
            'popular_greenwood_ad' => ['name' => 'Popular Properties Near Greenwood Ad', 'w' => 382, 'h' => 749],
            'popular_properties_ad' => ['name' => 'Popular Properties Ad', 'w' => 462, 'h' => 413],
            'below_popular_ad' => ['name' => 'Below Popular Properties Ad', 'w' => 1232, 'h' => 145],
            'builders_developers_ad' => ['name' => 'Builders & Developers Ad', 'w' => 292, 'h' => 271],
            'below_builders_ad' => ['name' => 'Below Builders & Developers Ad', 'w' => 1232, 'h' => 145],
        ];
    }
}
