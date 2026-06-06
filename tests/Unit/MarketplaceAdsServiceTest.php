<?php

namespace Tests\Unit;

use App\Models\AdSize;
use App\Models\UserAd;
use App\Services\MarketplaceAdsService;
use PHPUnit\Framework\TestCase;

class MarketplaceAdsServiceTest extends TestCase
{
    public function test_service_page_ads_hide_standard_square_but_keep_large_square(): void
    {
        $fullPage = $this->makeAd('full_page', 900, 1200);
        $horizontal = $this->makeAd('horizontal', 900, 600);
        $vertical = $this->makeAd('vertical_rectangle', 600, 900);
        $square = $this->makeAd('square', 640, 640);
        $largeSquare = $this->makeAd('square_large', 900, 900);

        $placements = (new MarketplaceAdsService)->splitServicePageAds(collect([
            $square,
            $horizontal,
            $fullPage,
            $largeSquare,
            $vertical,
        ]));

        $this->assertSame([$fullPage], $placements['full_page']->all());
        $this->assertSame([$horizontal, $largeSquare, $vertical], $placements['supporting']->all());
    }

    public function test_service_page_ad_split_supports_legacy_full_size_key_without_a_loaded_relation(): void
    {
        $legacyFullSize = new UserAd(['size_type' => 'full-size']);

        $placements = (new MarketplaceAdsService)->splitServicePageAds(collect([$legacyFullSize]));

        $this->assertSame([$legacyFullSize], $placements['full_page']->all());
        $this->assertTrue($placements['supporting']->isEmpty());
    }

    private function makeAd(string $sizeKey, int $width, int $height): UserAd
    {
        $ad = new UserAd(['size_type' => $sizeKey]);
        $ad->setRelation('adSize', new AdSize([
            'size_key' => $sizeKey,
            'width' => $width,
            'height' => $height,
        ]));

        return $ad;
    }
}
