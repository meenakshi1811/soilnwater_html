<?php

namespace App\Support;

use App\Models\ServiceProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class HomepagePopularServicesCards
{
    /**
     * @return list<array{
     *     url: string,
     *     image: string,
     *     title: string,
     *     description: string,
     *     location: string,
     *     rating_score: float,
     *     rating_count: int,
     *     category_label: string,
     *     category_tone: string,
     *     category_icon: string
     * }>
     */
    public static function build(Collection $serviceProviders, bool $hasLocation, int $limit = 4): array
    {
        $cards = [];

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

        if (count($cards) >= $limit) {
            return array_slice($cards, 0, $limit);
        }

        return array_slice(array_merge($cards, self::fallbackCards()), 0, $limit);
    }

    /**
     * @return array{
     *     url: string,
     *     image: string,
     *     title: string,
     *     description: string,
     *     location: string,
     *     rating_score: float,
     *     rating_count: int,
     *     category_label: string,
     *     category_tone: string,
     *     category_icon: string
     * }|null
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

        if (str_contains($name, 'ac ') || str_contains($name, 'hvac') || str_contains($name, 'air condition') || str_contains($name, 'cooling')) {
            return ['label' => 'AC Service', 'tone' => 'blue', 'icon' => 'fa-snowflake'];
        }

        if (str_contains($name, 'plumb') || str_contains($name, 'pipe') || str_contains($name, 'sanitary')) {
            return ['label' => 'Plumbing', 'tone' => 'orange', 'icon' => 'fa-faucet-drip'];
        }

        if (str_contains($name, 'electric') || str_contains($name, 'wiring')) {
            return ['label' => 'Electrical Services', 'tone' => 'purple', 'icon' => 'fa-bolt'];
        }

        if (str_contains($name, 'clean') || str_contains($name, 'housekeep') || str_contains($name, 'maid')) {
            return ['label' => 'Home Cleaning', 'tone' => 'pink', 'icon' => 'fa-broom'];
        }

        if (str_contains($name, 'carpenter') || str_contains($name, 'furniture')) {
            return ['label' => 'Carpentry', 'tone' => 'amber', 'icon' => 'fa-hammer'];
        }

        if (str_contains($name, 'paint')) {
            return ['label' => 'Painting', 'tone' => 'teal', 'icon' => 'fa-paint-roller'];
        }

        if (str_contains($name, 'pest')) {
            return ['label' => 'Pest Control', 'tone' => 'green', 'icon' => 'fa-bug-slash'];
        }

        return [
            'label' => Str::limit($rawName !== '' ? $rawName : 'Services', 22, ''),
            'tone' => 'slate',
            'icon' => 'fa-screwdriver-wrench',
        ];
    }

    /**
     * @return list<array{
     *     url: string,
     *     image: string,
     *     title: string,
     *     description: string,
     *     location: string,
     *     rating_score: float,
     *     rating_count: int,
     *     category_label: string,
     *     category_tone: string,
     *     category_icon: string
     * }>
     */
    private static function fallbackCards(): array
    {
        $indexUrl = route('frontend.service_providers.index');

        return [
            [
                'url' => $indexUrl,
                'image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=800&q=80',
                'title' => 'CoolFix Services',
                'description' => 'AC Installation, Repair & Maintenance',
                'location' => 'Rajpur Road, Dehradun',
                'rating_score' => 4.6,
                'rating_count' => 124,
                'category_label' => 'AC Service',
                'category_tone' => 'blue',
                'category_icon' => 'fa-snowflake',
            ],
            [
                'url' => $indexUrl,
                'image' => 'https://images.unsplash.com/photo-1607472586893-edb57bdc0e39?w=800&q=80',
                'title' => 'Sharma Plumbing Works',
                'description' => 'Plumbing Installation & Repair',
                'location' => 'Dalanwala, Dehradun',
                'rating_score' => 4.4,
                'rating_count' => 86,
                'category_label' => 'Plumbing',
                'category_tone' => 'orange',
                'category_icon' => 'fa-faucet-drip',
            ],
            [
                'url' => $indexUrl,
                'image' => 'https://images.unsplash.com/photo-1621905252507-b35492cc74b4?w=800&q=80',
                'title' => 'Verma Electricals',
                'description' => 'Wiring, Repair & Electrical Solutions',
                'location' => 'Patel Nagar, Dehradun',
                'rating_score' => 4.5,
                'rating_count' => 112,
                'category_label' => 'Electrical Services',
                'category_tone' => 'purple',
                'category_icon' => 'fa-bolt',
            ],
            [
                'url' => $indexUrl,
                'image' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?w=800&q=80',
                'title' => 'HomeCare Solutions',
                'description' => 'Home & Office Cleaning Services',
                'location' => 'Dehradun',
                'rating_score' => 4.3,
                'rating_count' => 95,
                'category_label' => 'Home Cleaning',
                'category_tone' => 'pink',
                'category_icon' => 'fa-broom',
            ],
            [
                'url' => $indexUrl,
                'image' => 'https://images.unsplash.com/photo-1581092918056-0e12f16c08ab?w=800&q=80',
                'title' => 'QuickFix Repair Services',
                'description' => 'Appliance repair, maintenance & installation',
                'location' => 'Dehradun',
                'rating_score' => 4.6,
                'rating_count' => 102,
                'category_label' => 'Repair & Maintenance',
                'category_tone' => 'red',
                'category_icon' => 'fa-screwdriver-wrench',
            ],
            [
                'url' => route('frontend.consultants.index'),
                'image' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=800&q=80',
                'title' => 'Legal & Consulting Services',
                'description' => 'Legal, tax, financial & professional advisory',
                'location' => 'Dehradun',
                'rating_score' => 4.7,
                'rating_count' => 94,
                'category_label' => 'Professional Services',
                'category_tone' => 'pink',
                'category_icon' => 'fa-briefcase',
            ],
        ];
    }
}
