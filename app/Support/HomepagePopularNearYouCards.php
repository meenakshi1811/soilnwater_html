<?php

namespace App\Support;

use App\Models\ServiceProvider;
use App\Models\Vendor;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class HomepagePopularNearYouCards
{
    /**
     * @return list<array{url: string, image: string, title: string, description: string, location: string, rating_score: float, rating_count: int, category_label: string, category_tone: string, category_icon: string, offer_badge: ?string, offer_badge_highlight: ?string}>
     */
    public static function build(Collection $vendors, Collection $serviceProviders, bool $hasLocation, int $limit = 8): array
    {
        $cards = [];

        foreach ($vendors as $vendor) {
            if (! $vendor instanceof Vendor) {
                continue;
            }

            $mapped = self::mapVendor($vendor, $hasLocation);
            if ($mapped !== null) {
                $cards[] = $mapped;
            }

            if (count($cards) >= $limit) {
                return $cards;
            }
        }

        foreach ($serviceProviders as $serviceProvider) {
            if (! $serviceProvider instanceof ServiceProvider) {
                continue;
            }

            $mapped = self::mapServiceProvider($serviceProvider, $hasLocation);
            if ($mapped !== null) {
                $cards[] = $mapped;
            }

            if (count($cards) >= $limit) {
                return $cards;
            }
        }

        if (count($cards) >= 4) {
            return array_slice($cards, 0, $limit);
        }

        return array_slice(array_merge($cards, self::fallbackCards()), 0, $limit);
    }

    /**
     * @return array{url: string, image: string, title: string, description: string, location: string, rating_score: float, rating_count: int, category_label: string, category_tone: string, category_icon: string, offer_badge: ?string, offer_badge_highlight: ?string}|null
     */
    private static function mapVendor(Vendor $vendor, bool $hasLocation): ?array
    {
        $card = VendorListingCard::data($vendor, $hasLocation);
        $categoryMeta = self::categoryMeta($card['categoryName']);

        $description = $card['featuredLabel'] ?? '';
        if ($description === '' && ! empty($card['serviceTags'])) {
            $description = implode(', ', array_slice($card['serviceTags'], 0, 3));
        }

        return [
            'url' => $card['storeUrl'],
            'image' => $card['coverImage'],
            'title' => $vendor->publicDisplayName(),
            'description' => $description !== '' ? $description : 'Explore products and services',
            'location' => self::shortLocation($card['locationLabel']),
            'rating_score' => $card['ratingScore'],
            'rating_count' => $card['ratingCount'],
            'category_label' => $categoryMeta['label'],
            'category_tone' => $categoryMeta['tone'],
            'category_icon' => $categoryMeta['icon'],
            'offer_badge' => null,
            'offer_badge_highlight' => null,
        ];
    }

    /**
     * @return array{url: string, image: string, title: string, description: string, location: string, rating_score: float, rating_count: int, category_label: string, category_tone: string, category_icon: string, offer_badge: ?string, offer_badge_highlight: ?string}|null
     */
    private static function mapServiceProvider(ServiceProvider $serviceProvider, bool $hasLocation): ?array
    {
        $card = ServiceProviderListingCard::data($serviceProvider, $hasLocation);
        $categoryMeta = self::categoryMeta($card['categoryName']);
        $description = $card['featuredLabel'] ?? '';
        if ($description === '' && ! empty($card['serviceTags'])) {
            $description = implode(', ', array_slice($card['serviceTags'], 0, 3));
        }

        return [
            'url' => $card['profileUrl'],
            'image' => $card['coverImage'],
            'title' => $serviceProvider->publicDisplayName(),
            'description' => $description !== '' ? $description : 'Professional services near you',
            'location' => self::shortLocation($card['locationLabel']),
            'rating_score' => $card['ratingScore'],
            'rating_count' => $card['ratingCount'],
            'category_label' => $categoryMeta['label'],
            'category_tone' => $categoryMeta['tone'],
            'category_icon' => $categoryMeta['icon'],
            'offer_badge' => null,
            'offer_badge_highlight' => null,
        ];
    }

    private static function shortLocation(string $label): string
    {
        $parts = array_map('trim', explode(',', $label));

        return implode(', ', array_slice($parts, 0, 2));
    }

    /**
     * @return array{label: string, tone: string, icon: string}
     */
    private static function categoryMeta(string $rawName): array
    {
        $name = strtolower($rawName);

        if (str_contains($name, 'restaurant') || str_contains($name, 'food') || str_contains($name, 'cafe')) {
            return ['label' => 'Restaurant', 'tone' => 'orange', 'icon' => 'fa-utensils'];
        }

        if (str_contains($name, 'ac ') || str_contains($name, 'hvac') || str_contains($name, 'cooling') || str_contains($name, 'repair')) {
            return ['label' => 'AC Service', 'tone' => 'blue', 'icon' => 'fa-screwdriver-wrench'];
        }

        if (str_contains($name, 'dental') || str_contains($name, 'clinic') || str_contains($name, 'health')) {
            return ['label' => 'Dental Clinic', 'tone' => 'pink', 'icon' => 'fa-tooth'];
        }

        if (str_contains($name, 'grocery') || str_contains($name, 'mart') || str_contains($name, 'supermarket')) {
            return ['label' => 'Grocery Store', 'tone' => 'green', 'icon' => 'fa-cart-shopping'];
        }

        if (str_contains($name, 'salon') || str_contains($name, 'spa') || str_contains($name, 'beauty')) {
            return ['label' => 'Salon & Spa', 'tone' => 'purple', 'icon' => 'fa-spa'];
        }

        if (str_contains($name, 'electronic') || str_contains($name, 'mobile') || str_contains($name, 'tech')) {
            return ['label' => 'Electronics', 'tone' => 'indigo', 'icon' => 'fa-laptop'];
        }

        return [
            'label' => Str::limit($rawName, 16, ''),
            'tone' => 'slate',
            'icon' => 'fa-store',
        ];
    }

    /**
     * @return list<array{url: string, image: string, title: string, description: string, location: string, rating_score: float, rating_count: int, category_label: string, category_tone: string, category_icon: string, offer_badge: ?string, offer_badge_highlight: ?string}>
     */
    private static function fallbackCards(): array
    {
        $vendorsUrl = route('frontend.vendors.index');

        return [
            [
                'url' => $vendorsUrl,
                'image' => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=800&q=80',
                'title' => 'Spice Garden',
                'description' => 'North Indian, Chinese & Continental',
                'location' => 'Rajpur Road, Dehradun',
                'rating_score' => 4.5,
                'rating_count' => 182,
                'category_label' => 'Restaurant',
                'category_tone' => 'orange',
                'category_icon' => 'fa-utensils',
                'offer_badge' => '20% OFF',
                'offer_badge_highlight' => null,
            ],
            [
                'url' => route('frontend.service_providers.index'),
                'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800&q=80',
                'title' => 'CoolFix Services',
                'description' => 'AC Installation, Repair & Maintenance',
                'location' => 'Dalanwala, Dehradun',
                'rating_score' => 4.6,
                'rating_count' => 124,
                'category_label' => 'AC Service',
                'category_tone' => 'blue',
                'category_icon' => 'fa-screwdriver-wrench',
                'offer_badge' => null,
                'offer_badge_highlight' => null,
            ],
            [
                'url' => $vendorsUrl,
                'image' => 'https://images.unsplash.com/photo-1629909613654-28e377c037b2?w=800&q=80',
                'title' => 'Smile Care Dental Clinic',
                'description' => 'General & Cosmetic Dentistry',
                'location' => 'Ballupur Chowk, Dehradun',
                'rating_score' => 4.4,
                'rating_count' => 98,
                'category_label' => 'Dental Clinic',
                'category_tone' => 'pink',
                'category_icon' => 'fa-tooth',
                'offer_badge' => null,
                'offer_badge_highlight' => null,
            ],
            [
                'url' => $vendorsUrl,
                'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800&q=80',
                'title' => 'FreshMart Supermarket',
                'description' => 'Daily Essentials, Fresh Fruits & Vegetables',
                'location' => 'ISBT Road, Dehradun',
                'rating_score' => 4.3,
                'rating_count' => 156,
                'category_label' => 'Grocery Store',
                'category_tone' => 'green',
                'category_icon' => 'fa-cart-shopping',
                'offer_badge' => 'BUY 1 GET 1',
                'offer_badge_highlight' => 'FREE',
            ],
        ];
    }
}
