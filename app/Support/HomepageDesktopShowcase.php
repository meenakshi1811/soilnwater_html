<?php

namespace App\Support;

final class HomepageDesktopShowcase
{
    private const HERO_BG = 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?auto=format&fit=crop&w=1920&q=80';

    /**
     * @return array<string, mixed>
     */
    public static function section(string $key): array
    {
        $section = match ($key) {
            'offers' => [
                'key' => 'offers',
                'title' => 'Latest',
                'title_accent' => 'Offers & Discounts',
                'subtitle' => 'Great deals from local businesses in Dehradun',
                'hero_bg' => self::HERO_BG,
                'icon' => 'fa-tags',
                'cta_label' => 'See All Offers',
                'cta_url' => route('frontend.offers.index'),
                'footer_mode' => 'offer',
                'chips' => [
                    ['icon' => 'fa-tag', 'tone' => 'red', 'label' => 'Exclusive Offers'],
                    ['icon' => 'fa-piggy-bank', 'tone' => 'orange', 'label' => 'Save More'],
                    ['icon' => 'fa-store', 'tone' => 'pink', 'label' => 'Support Local Businesses'],
                    ['icon' => 'fa-wand-magic-sparkles', 'tone' => 'blue', 'label' => 'New Offers Regularly'],
                ],
            ],
            'ads' => [
                'key' => 'ads',
                'title' => 'Latest',
                'title_accent' => 'Ads & Listings',
                'subtitle' => 'Discover sponsored listings and marketplace ads near you',
                'hero_bg' => self::HERO_BG,
                'icon' => 'fa-rectangle-ad',
                'cta_label' => 'See All Ads',
                'cta_url' => route('frontend.ads.index'),
                'footer_mode' => 'offer',
                'chips' => [
                    ['icon' => 'fa-bullhorn', 'tone' => 'blue', 'label' => 'Fresh Listings'],
                    ['icon' => 'fa-location-dot', 'tone' => 'green', 'label' => 'Near You'],
                    ['icon' => 'fa-shield-check', 'tone' => 'teal', 'label' => 'Verified Posters'],
                    ['icon' => 'fa-clock', 'tone' => 'purple', 'label' => 'Updated Daily'],
                ],
            ],
            'popular-near' => [
                'key' => 'popular-near',
                'title' => 'Popular',
                'title_accent' => 'Near You',
                'subtitle' => 'Top-rated businesses and services around you in Dehradun',
                'hero_bg' => self::HERO_BG,
                'icon' => 'fa-location-dot',
                'icon_style' => 'pin-ring',
                'cta_label' => 'See All Nearby',
                'cta_url' => route('frontend.businesses.hub'),
                'footer_mode' => 'rating',
                'chips' => [
                    ['icon' => 'fa-location-dot', 'tone' => 'green', 'label' => 'Nearby Locations'],
                    ['icon' => 'fa-star', 'tone' => 'orange', 'label' => 'Top Rated'],
                    ['icon' => 'fa-shield-check', 'tone' => 'blue', 'label' => 'Verified Businesses'],
                    ['icon' => 'fa-clock', 'tone' => 'purple', 'label' => 'Quick & Convenient'],
                ],
            ],
            'featured-businesses' => [
                'key' => 'featured-businesses',
                'title' => 'Featured',
                'title_accent' => 'Businesses',
                'subtitle' => 'Discover trusted local businesses offering the best products and services',
                'hero_bg' => self::HERO_BG,
                'icon' => 'fa-award',
                'icon_style' => 'award',
                'cta_label' => 'See All Businesses',
                'cta_url' => route('frontend.vendors.index'),
                'footer_mode' => 'rating',
                'chips' => [],
            ],
            'services' => [
                'key' => 'services',
                'title' => 'Popular',
                'title_accent' => 'Services',
                'subtitle' => 'Find trusted service providers near you in Dehradun',
                'hero_bg' => self::HERO_BG,
                'icon' => 'fa-screwdriver-wrench',
                'cta_label' => 'See All Services',
                'cta_url' => route('frontend.service_providers.index'),
                'footer_mode' => 'rating',
                'chips' => [
                    ['icon' => 'fa-circle-check', 'tone' => 'green', 'label' => 'Verified Providers'],
                    ['icon' => 'fa-location-dot', 'tone' => 'orange', 'label' => 'Local & Reliable'],
                    ['icon' => 'fa-users', 'tone' => 'purple', 'label' => 'Wide Range of Services'],
                    ['icon' => 'fa-clock', 'tone' => 'blue', 'label' => 'Quick & Convenient'],
                ],
            ],
            'education' => [
                'key' => 'education',
                'title' => 'Education &',
                'title_accent' => 'Knowledge',
                'subtitle' => 'Learn, Grow and Build a Better Future',
                'hero_bg' => self::HERO_BG,
                'icon' => 'fa-graduation-cap',
                'cta_label' => 'See All Education & Knowledge',
                'cta_url' => route('schools.index'),
                'footer_mode' => 'rating',
                'chips' => [
                    ['icon' => 'fa-book', 'tone' => 'blue', 'label' => 'Quality Learning'],
                    ['icon' => 'fa-user-group', 'tone' => 'purple', 'label' => 'Expert Guidance'],
                    ['icon' => 'fa-lightbulb', 'tone' => 'amber', 'label' => 'Useful Resources'],
                    ['icon' => 'fa-chart-column', 'tone' => 'teal', 'label' => 'For All Age Groups'],
                ],
            ],
            'study-material' => [
                'key' => 'study-material',
                'title' => 'Study Material',
                'title_accent' => 'Library',
                'subtitle' => 'Learn • Practice • Prepare | Books • Notes • Question Papers & More',
                'hero_bg' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&w=1920&q=80',
                'icon' => 'fa-book',
                'cta_label' => 'See All Study Material',
                'cta_url' => route('study-materials.library'),
                'footer_mode' => 'study',
                'chips' => [
                    ['icon' => 'fa-graduation-cap', 'tone' => 'green', 'label' => 'School & Board Exams'],
                    ['icon' => 'fa-building-columns', 'tone' => 'purple', 'label' => 'University Exams'],
                    ['icon' => 'fa-award', 'tone' => 'orange', 'label' => 'Competitive Exams'],
                    ['icon' => 'fa-file-lines', 'tone' => 'blue', 'label' => 'Free & Useful Resources'],
                ],
            ],
            'consultants' => [
                'key' => 'consultants',
                'title' => 'Consultants &',
                'title_accent' => 'Professionals',
                'subtitle' => 'Get Expert Advice for Your Personal, Business & Property Needs',
                'hero_bg' => self::HERO_BG,
                'icon' => 'fa-user-tie',
                'cta_label' => 'See All Consultants',
                'cta_url' => route('frontend.consultants.index'),
                'footer_mode' => 'rating',
                'cta_card' => 'View Profile',
                'chips' => [
                    ['icon' => 'fa-circle-check', 'tone' => 'orange', 'label' => 'Verified Experts'],
                    ['icon' => 'fa-layer-group', 'tone' => 'purple', 'label' => 'Wide Range of Services'],
                    ['icon' => 'fa-location-dot', 'tone' => 'green', 'label' => 'Local & Online Consultancy'],
                    ['icon' => 'fa-handshake', 'tone' => 'blue', 'label' => 'Trusted & Reliable'],
                ],
            ],
            'community' => [
                'key' => 'community',
                'title' => 'SoilnWater',
                'title_accent' => 'Community',
                'subtitle' => 'Connect • Discuss • Share • Learn • Grow Together',
                'hero_bg' => self::HERO_BG,
                'icon' => 'fa-people-group',
                'cta_label' => 'Explore Community',
                'cta_url' => route('community.index'),
                'footer_mode' => 'community',
                'chips' => [
                    ['icon' => 'fa-circle-question', 'tone' => 'blue', 'label' => 'Ask Questions'],
                    ['icon' => 'fa-share-nodes', 'tone' => 'orange', 'label' => 'Share Knowledge'],
                    ['icon' => 'fa-user-graduate', 'tone' => 'purple', 'label' => 'Get Expert Advice'],
                    ['icon' => 'fa-heart', 'tone' => 'red', 'label' => 'Build Connections'],
                ],
            ],
            default => [
                'key' => $key,
                'title' => 'Discover',
                'title_accent' => 'SoilnWater',
                'subtitle' => '',
                'hero_bg' => self::HERO_BG,
                'icon' => 'fa-compass',
                'cta_label' => 'See All',
                'cta_url' => route('frontend.index'),
                'footer_mode' => 'rating',
                'chips' => [],
            ],
        };

        $defaultHero = $section['hero_bg'] ?? self::HERO_BG;
        $section['hero_bg'] = HomepageSectionHero::backgroundUrl($key, $defaultHero);

        return $section;
    }

    /**
     * @param  list<array<string, mixed>>  $cards
     * @return list<array<string, mixed>>
     */
    public static function prepareCards(string $sectionKey, array $cards, int $limit = 6): array
    {
        $section = self::section($sectionKey);
        $defaultCta = $section['cta_card'] ?? match ($sectionKey) {
            'offers' => 'View Offer',
            'ads' => 'View Ad',
            'study-material' => 'View',
            'community' => 'Explore',
            default => 'View Details',
        };

        $prepared = [];

        foreach (array_slice($cards, 0, $limit) as $card) {
            if ($sectionKey === 'study-material') {
                $card['category_label'] = $card['category_label'] ?? $card['badge_label'] ?? '';
                $card['category_icon'] = $card['category_icon'] ?? $card['badge_icon'] ?? 'fa-book';
                $card['category_tone'] = $card['category_tone'] ?? $card['badge_tone'] ?? 'slate';
                $card['description'] = $card['description'] ?? $card['subtitle'] ?? '';
            }

            $tone = (string) ($card['category_tone'] ?? 'slate');
            $prepared[] = array_merge($card, [
                'cta_label' => $card['cta_label'] ?? self::ctaForCard($sectionKey, $card, $defaultCta),
                'category_tone' => $tone,
                'rating_score' => (float) ($card['rating_score'] ?? 0),
                'rating_count' => (int) ($card['rating_count'] ?? 0),
                'footer_note' => $card['footer_note'] ?? $card['offer_meta'] ?? $card['valid_till'] ?? null,
                'stat_primary' => $card['stat_primary'] ?? null,
                'stat_secondary' => $card['stat_secondary'] ?? null,
            ]);
        }

        return $prepared;
    }

    /**
     * @param  array<string, mixed>  $card
     */
    private static function ctaForCard(string $sectionKey, array $card, string $default): string
    {
        if ($sectionKey === 'study-material' && ! empty($card['badge_label'])) {
            return match (true) {
                str_contains(strtolower($card['badge_label']), 'question') => 'View Papers',
                str_contains(strtolower($card['badge_label']), 'solved') => 'View Solutions',
                str_contains(strtolower($card['badge_label']), 'notes') => 'View Notes',
                str_contains(strtolower($card['badge_label']), 'reference') => 'View Resources',
                str_contains(strtolower($card['badge_label']), 'competitive') => 'View Material',
                str_contains(strtolower($card['badge_label']), 'course') => 'Explore Courses',
                default => 'View Details',
            };
        }

        if ($sectionKey === 'education' && ! empty($card['category_label'])) {
            return match (true) {
                str_contains(strtolower($card['category_label']), 'article') => 'Read Articles',
                str_contains(strtolower($card['category_label']), 'career') => 'Get Guidance',
                str_contains(strtolower($card['category_label']), 'online') => 'Explore Courses',
                str_contains(strtolower($card['category_label']), 'study material') => 'Access Library',
                default => 'View Details',
            };
        }

        if ($sectionKey === 'community') {
            return 'Join Now';
        }

        return $default;
    }

    /**
     * @param  list<array<string, mixed>>  $businesses
     * @return list<array<string, mixed>>
     */
    public static function featuredBusinessCards(array $businesses): array
    {
        $cards = [];

        foreach ($businesses as $business) {
            $cards[] = [
                'url' => $business['url'],
                'image' => $business['image'],
                'title' => $business['name'],
                'description' => $business['category'] ?? '',
                'location' => $business['location'] ?? 'Dehradun',
                'rating_score' => (float) ($business['rating'] ?? 4.5),
                'rating_count' => (int) ($business['reviews'] ?? 0),
                'category_label' => $business['category'] ?? 'Business',
                'category_tone' => $business['theme'] ?? 'slate',
                'category_icon' => 'fa-store',
            ];
        }

        return self::prepareCards('featured-businesses', $cards);
    }
}
