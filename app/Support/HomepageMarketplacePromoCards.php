<?php

namespace App\Support;

use App\Models\Offer;
use App\Models\UserAd;
use Illuminate\Support\Collection;

final class HomepageMarketplacePromoCards
{
    public static function fromOffers(Collection $offers, int $limit = 8): array
    {
        $cards = $offers
            ->take($limit)
            ->map(fn (Offer $offer) => self::mapOffer($offer))
            ->filter(fn (?array $card) => $card !== null)
            ->values()
            ->all();

        if (count($cards) >= 4) {
            return $cards;
        }

        return array_slice(array_merge($cards, self::fallbackOfferCards()), 0, $limit);
    }

    public static function fromAds(Collection $ads, int $limit = 8): array
    {
        $cards = $ads
            ->take($limit)
            ->map(fn (UserAd $ad) => self::mapAd($ad))
            ->filter(fn (?array $card) => $card !== null)
            ->values()
            ->all();

        if (count($cards) >= 4) {
            return $cards;
        }

        return array_slice(array_merge($cards, self::fallbackAdCards()), 0, $limit);
    }

    /**
     * @return array{url: string, image: string, discount_badge: string, category_label: string, category_tone: string, category_icon: string, title: string, description: string, location: string, valid_till: string}|null
     */
    private static function mapOffer(Offer $offer): ?array
    {
        $entityCover = filled($offer->banner_image)
            ? asset($offer->banner_image)
            : asset('assets/images/vendor-card-placeholder.svg');
        $image = HomepageCategoryCardImage::forOffer($offer, $entityCover);

        $categoryName = $offer->category?->name ?? $offer->subcategory?->name ?? 'Local offer';
        $categoryMeta = self::categoryMeta($categoryName);

        return [
            'url' => $offer->shareUrl(),
            'image' => $image,
            'discount_badge' => self::formatDiscountBadge($offer->discount_tag, $offer->title),
            'category_label' => $categoryMeta['label'],
            'category_tone' => $categoryMeta['tone'],
            'category_icon' => $categoryMeta['icon'],
            'title' => self::businessTitleFromOffer($offer),
            'description' => filled($offer->short_description)
                ? (string) $offer->short_description
                : $offer->title,
            'location' => filled($offer->location) ? (string) $offer->location : 'Near you',
            'valid_till' => $offer->valid_until?->format('d M Y') ?? 'Limited time',
        ];
    }

    /**
     * @return array{url: string, image: string, discount_badge: string, category_label: string, category_tone: string, category_icon: string, title: string, description: string, location: string, valid_till: string}|null
     */
    private static function mapAd(UserAd $ad): ?array
    {
        $entityCover = filled($ad->final_image) ? asset($ad->final_image) : '';
        $image = HomepageCategoryCardImage::forUserAd($ad, $entityCover);
        if ($image === '') {
            return null;
        }

        $categoryName = $ad->category?->name ?? 'Featured ad';
        $categoryMeta = self::categoryMeta($categoryName);

        return [
            'url' => $ad->shareUrl(),
            'image' => $image,
            'discount_badge' => self::formatDiscountBadge(null, $ad->title),
            'category_label' => $categoryMeta['label'],
            'category_tone' => $categoryMeta['tone'],
            'category_icon' => $categoryMeta['icon'],
            'title' => $ad->title,
            'description' => filled($ad->short_description)
                ? (string) $ad->short_description
                : 'Browse this listing on SoilnWater',
            'location' => filled($ad->location) ? (string) $ad->location : 'Near you',
            'valid_till' => $ad->valid_until?->format('d M Y') ?? 'No expiry',
        ];
    }

    private static function businessTitleFromOffer(Offer $offer): string
    {
        $title = trim($offer->title);
        if ($title === '') {
            return 'Local business offer';
        }

        if (str_contains($title, '—')) {
            return trim(explode('—', $title)[0]);
        }

        if (str_contains($title, '-')) {
            return trim(explode('-', $title)[0]);
        }

        return $title;
    }

    private static function formatDiscountBadge(?string $discountTag, string $title): string
    {
        if (filled($discountTag)) {
            return strtoupper(trim($discountTag));
        }

        if (preg_match('/\d+\s*%/i', $title, $matches)) {
            return strtoupper($matches[0].' OFF');
        }

        if (stripos($title, 'buy') !== false && stripos($title, 'get') !== false) {
            return 'BUY 1 GET 1 FREE';
        }

        return 'SPECIAL OFFER';
    }

    /**
     * @return array{label: string, tone: string, icon: string}
     */
    private static function categoryMeta(string $rawName): array
    {
        $name = strtolower($rawName);

        if (str_contains($name, 'restaurant') || str_contains($name, 'food') || str_contains($name, 'cafe')) {
            return ['label' => 'Restaurant', 'tone' => 'brown', 'icon' => 'fa-utensils'];
        }

        if (str_contains($name, 'salon') || str_contains($name, 'spa') || str_contains($name, 'beauty')) {
            return ['label' => 'Salon & Spa', 'tone' => 'purple', 'icon' => 'fa-spa'];
        }

        if (str_contains($name, 'grocery') || str_contains($name, 'mart') || str_contains($name, 'supermarket')) {
            return ['label' => 'Grocery Store', 'tone' => 'green', 'icon' => 'fa-cart-shopping'];
        }

        if (str_contains($name, 'electronic') || str_contains($name, 'mobile') || str_contains($name, 'tech')) {
            return ['label' => 'Electronics', 'tone' => 'blue', 'icon' => 'fa-laptop'];
        }

        if (str_contains($name, 'property') || str_contains($name, 'real estate')) {
            return ['label' => 'Property', 'tone' => 'teal', 'icon' => 'fa-building'];
        }

        return [
            'label' => \Illuminate\Support\Str::limit($rawName, 18, ''),
            'tone' => 'slate',
            'icon' => 'fa-store',
        ];
    }

    /**
     * @return list<array{url: string, image: string, discount_badge: string, category_label: string, category_tone: string, category_icon: string, title: string, description: string, location: string, valid_till: string}>
     */
    private static function fallbackOfferCards(): array
    {
        $url = route('frontend.offers.index');

        return [
            [
                'url' => $url,
                'image' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=800&q=80',
                'discount_badge' => '20% OFF',
                'category_label' => 'Restaurant',
                'category_tone' => 'brown',
                'category_icon' => 'fa-utensils',
                'title' => 'Spice Garden',
                'description' => 'North Indian, Chinese & Continental',
                'location' => 'Rajpur Road, Dehradun',
                'valid_till' => '30 Sep 2026',
            ],
            [
                'url' => $url,
                'image' => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800&q=80',
                'discount_badge' => '30% OFF',
                'category_label' => 'Salon & Spa',
                'category_tone' => 'purple',
                'category_icon' => 'fa-spa',
                'title' => 'Style & Glow Salon',
                'description' => 'Hair, skin & bridal packages',
                'location' => 'Dalanwala, Dehradun',
                'valid_till' => '15 Oct 2026',
            ],
            [
                'url' => $url,
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&q=80',
                'discount_badge' => 'BUY 1 GET 1 FREE',
                'category_label' => 'Grocery Store',
                'category_tone' => 'green',
                'category_icon' => 'fa-cart-shopping',
                'title' => 'FreshMart Supermarket',
                'description' => 'Daily essentials & fresh produce',
                'location' => 'ISBT Road, Dehradun',
                'valid_till' => '20 Oct 2026',
            ],
            [
                'url' => $url,
                'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&q=80',
                'discount_badge' => 'UP TO 40% OFF',
                'category_label' => 'Electronics',
                'category_tone' => 'blue',
                'category_icon' => 'fa-laptop',
                'title' => 'TechWorld Electronics',
                'description' => 'TVs, laptops, mobiles & accessories',
                'location' => 'Rajpur Road, Dehradun',
                'valid_till' => '31 Dec 2026',
            ],
        ];
    }

    /**
     * @return list<array{url: string, image: string, discount_badge: string, category_label: string, category_tone: string, category_icon: string, title: string, description: string, location: string, valid_till: string}>
     */
    private static function fallbackAdCards(): array
    {
        $url = route('frontend.ads.index');

        return [
            [
                'url' => $url,
                'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&q=80',
                'discount_badge' => 'FEATURED',
                'category_label' => 'Office Space',
                'category_tone' => 'teal',
                'category_icon' => 'fa-building',
                'title' => 'Prime Co-working Hub',
                'description' => 'Flexible desks & meeting rooms',
                'location' => 'Civil Lines, Dehradun',
                'valid_till' => '30 Nov 2026',
            ],
            [
                'url' => $url,
                'image' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&q=80',
                'discount_badge' => 'NEW LISTING',
                'category_label' => 'Property',
                'category_tone' => 'slate',
                'category_icon' => 'fa-house',
                'title' => 'Green Valley Apartments',
                'description' => '2 & 3 BHK homes with amenities',
                'location' => 'Sahastradhara Road',
                'valid_till' => '15 Jan 2027',
            ],
            [
                'url' => $url,
                'image' => 'https://images.unsplash.com/photo-1521737711867-e3b97375f020?w=800&q=80',
                'discount_badge' => 'HOT AD',
                'category_label' => 'Services',
                'category_tone' => 'purple',
                'category_icon' => 'fa-screwdriver-wrench',
                'title' => 'HomeCare Services',
                'description' => 'Repairs, cleaning & maintenance',
                'location' => 'Dehradun',
                'valid_till' => '28 Feb 2027',
            ],
            [
                'url' => $url,
                'image' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&q=80',
                'discount_badge' => 'SPONSORED',
                'category_label' => 'Restaurant',
                'category_tone' => 'brown',
                'category_icon' => 'fa-utensils',
                'title' => 'Mountain View Cafe',
                'description' => 'Family dining & party bookings',
                'location' => 'Mussoorie Road',
                'valid_till' => '10 Mar 2027',
            ],
        ];
    }
}
