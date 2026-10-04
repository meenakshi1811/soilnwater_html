<?php

namespace App\Support;

use App\Models\Offer;
use Illuminate\Support\Collection;

/**
 * Builds green "Shop popular deals" style blocks for the homepage.
 */
final class HomepageDealBlocks
{
    /**
     * @return list<array{key: string, title: string, title_html: string, tile_count: int, view_all_url: string, tiles: list<array{image: string, badge: string, url: string, alt: string}>}>
     */
    public static function build(Collection $offers): array
    {
        $offerTiles = $offers
            ->map(function (Offer $offer) {
                $image = filled($offer->banner_image)
                    ? asset($offer->banner_image)
                    : null;

                if (! $image) {
                    return null;
                }

                return [
                    'image' => $image,
                    'badge' => self::formatBadge($offer->discount_tag, $offer->title),
                    'url' => $offer->shareUrl(),
                    'alt' => $offer->title,
                ];
            })
            ->filter()
            ->values()
            ->all();

        $fallbackTiles = self::fallbackTiles();
        $pool = array_merge($offerTiles, $fallbackTiles);
        $poolCount = max(count($pool), 1);
        $cursor = 0;

        $blocks = [];

        foreach (self::definitions() as $definition) {
            $tileCount = $definition['tile_count'];
            $tiles = [];

            for ($i = 0; $i < $tileCount; $i++) {
                $tiles[] = $pool[$cursor % $poolCount];
                $cursor++;
            }

            $blocks[] = [
                'key' => $definition['key'],
                'title' => $definition['title'],
                'title_html' => $definition['title_html'],
                'tile_count' => $tileCount,
                'view_all_url' => $definition['view_all_url'],
                'tiles' => $tiles,
            ];
        }

        return $blocks;
    }

    /**
     * @return list<array{key: string, title: string, title_html: string, tile_count: int, view_all_url: string}>
     */
    private static function definitions(): array
    {
        $offersIndex = route('frontend.offers.index');

        return [
            [
                'key' => 'popular',
                'title' => 'Shop popular deals',
                'title_html' => 'Shop popular<br>deals',
                'tile_count' => 4,
                'view_all_url' => $offersIndex,
            ],
            [
                'key' => 'food',
                'title' => 'Food & dining',
                'title_html' => 'Food &amp;<br>dining',
                'tile_count' => 6,
                'view_all_url' => $offersIndex,
            ],
            [
                'key' => 'beauty',
                'title' => 'Beauty & wellness',
                'title_html' => 'Beauty &amp;<br>wellness',
                'tile_count' => 4,
                'view_all_url' => $offersIndex,
            ],
            [
                'key' => 'travel',
                'title' => 'Travel & stays',
                'title_html' => 'Travel &amp;<br>stays',
                'tile_count' => 6,
                'view_all_url' => $offersIndex,
            ],
            [
                'key' => 'fashion',
                'title' => 'Fashion & retail',
                'title_html' => 'Fashion &amp;<br>retail',
                'tile_count' => 4,
                'view_all_url' => $offersIndex,
            ],
            [
                'key' => 'grocery',
                'title' => 'Home & grocery',
                'title_html' => 'Home &amp;<br>grocery',
                'tile_count' => 6,
                'view_all_url' => $offersIndex,
            ],
            [
                'key' => 'electronics',
                'title' => 'Electronics deals',
                'title_html' => 'Electronics<br>deals',
                'tile_count' => 4,
                'view_all_url' => $offersIndex,
            ],
            [
                'key' => 'services',
                'title' => 'Services near you',
                'title_html' => 'Services<br>near you',
                'tile_count' => 6,
                'view_all_url' => $offersIndex,
            ],
            [
                'key' => 'weekend',
                'title' => 'Weekend specials',
                'title_html' => 'Weekend<br>specials',
                'tile_count' => 4,
                'view_all_url' => $offersIndex,
            ],
            [
                'key' => 'local',
                'title' => 'Local favorites',
                'title_html' => 'Local<br>favorites',
                'tile_count' => 6,
                'view_all_url' => $offersIndex,
            ],
        ];
    }

    /**
     * @return list<array{image: string, badge: string, url: string, alt: string}>
     */
    private static function fallbackTiles(): array
    {
        $offersIndex = route('frontend.offers.index');

        return [
            ['image' => 'https://images.unsplash.com/photo-1513104890138-7c749659a591?w=640&q=80', 'badge' => '20% OFF', 'url' => $offersIndex, 'alt' => 'Pizza offer'],
            ['image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=640&q=80', 'badge' => 'UP TO 50%', 'url' => $offersIndex, 'alt' => 'Resort offer'],
            ['image' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=640&q=80', 'badge' => '30% OFF', 'url' => $offersIndex, 'alt' => 'Salon offer'],
            ['image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=640&q=80', 'badge' => 'B1G1', 'url' => $offersIndex, 'alt' => 'Grocery offer'],
            ['image' => 'https://images.unsplash.com/photo-1441986300917-64676bd600d8?w=640&q=80', 'badge' => 'NEW', 'url' => $offersIndex, 'alt' => 'Fashion offer'],
            ['image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=640&q=80', 'badge' => 'SAVE ₹500', 'url' => $offersIndex, 'alt' => 'Electronics offer'],
            ['image' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=640&q=80', 'badge' => 'FLAT 15%', 'url' => $offersIndex, 'alt' => 'Restaurant offer'],
            ['image' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=640&q=80', 'badge' => 'SPA DEAL', 'url' => $offersIndex, 'alt' => 'Spa offer'],
            ['image' => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=640&q=80', 'badge' => 'HOT', 'url' => $offersIndex, 'alt' => 'Retail offer'],
            ['image' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?w=640&q=80', 'badge' => 'STAY 2N', 'url' => $offersIndex, 'alt' => 'Hotel offer'],
        ];
    }

    private static function formatBadge(?string $discountTag, string $title): string
    {
        if (filled($discountTag)) {
            return strtoupper(trim($discountTag));
        }

        return strtoupper(strtok($title, ' ') ?: 'DEAL');
    }
}
