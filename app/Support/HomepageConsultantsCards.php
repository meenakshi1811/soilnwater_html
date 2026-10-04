<?php

namespace App\Support;

use App\Models\Consultant;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class HomepageConsultantsCards
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
    public static function build(Collection $consultants, bool $hasLocation, int $limit = 4): array
    {
        $cards = [];

        foreach ($consultants as $consultant) {
            if (! $consultant instanceof Consultant) {
                continue;
            }

            $mapped = self::mapConsultant($consultant, $hasLocation);
            if ($mapped !== null) {
                $cards[] = $mapped;
            }

            if (count($cards) >= $limit) {
                return $cards;
            }
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
    private static function mapConsultant(Consultant $consultant, bool $hasLocation): ?array
    {
        $card = ConsultantListingCard::data($consultant, $hasLocation);
        $categoryMeta = self::categoryMeta($card['categoryName']);
        $description = $card['featuredLabel'] ?? '';
        if ($description === '' && ! empty($card['serviceTags'])) {
            $description = implode(', ', array_slice($card['serviceTags'], 0, 3));
        }

        return [
            'url' => $card['profileUrl'],
            'image' => $card['coverImage'],
            'title' => $consultant->publicDisplayName(),
            'description' => $description !== '' ? $description : 'Professional consulting services',
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

        if (str_contains($name, 'legal') || str_contains($name, 'law')) {
            return ['label' => 'Legal Advisory', 'tone' => 'indigo', 'icon' => 'fa-scale-balanced'];
        }

        if (str_contains($name, 'tax') || str_contains($name, 'account') || str_contains($name, 'finance')) {
            return ['label' => 'Tax & Finance', 'tone' => 'teal', 'icon' => 'fa-calculator'];
        }

        if (str_contains($name, 'business') || str_contains($name, 'startup')) {
            return ['label' => 'Business Consulting', 'tone' => 'blue', 'icon' => 'fa-briefcase'];
        }

        if (str_contains($name, 'career') || str_contains($name, 'hr')) {
            return ['label' => 'Career Guidance', 'tone' => 'purple', 'icon' => 'fa-user-tie'];
        }

        if (str_contains($name, 'real estate') || str_contains($name, 'property')) {
            return ['label' => 'Property Advisory', 'tone' => 'orange', 'icon' => 'fa-building'];
        }

        if (str_contains($name, 'health') || str_contains($name, 'medical')) {
            return ['label' => 'Health Consulting', 'tone' => 'pink', 'icon' => 'fa-heart-pulse'];
        }

        return [
            'label' => Str::limit($rawName !== '' ? $rawName : 'Consulting', 22, ''),
            'tone' => 'slate',
            'icon' => 'fa-user-tie',
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
        $indexUrl = route('frontend.consultants.index');

        return [
            [
                'url' => $indexUrl,
                'image' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?w=800&q=80',
                'title' => 'LegalEdge Advisors',
                'description' => 'Corporate Law, Contracts & Compliance',
                'location' => 'Rajpur Road, Dehradun',
                'rating_score' => 4.7,
                'rating_count' => 118,
                'category_label' => 'Legal Advisory',
                'category_tone' => 'indigo',
                'category_icon' => 'fa-scale-balanced',
            ],
            [
                'url' => $indexUrl,
                'image' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=800&q=80',
                'title' => 'FinTax Consultants',
                'description' => 'Tax Planning, GST & Accounting',
                'location' => 'Dalanwala, Dehradun',
                'rating_score' => 4.5,
                'rating_count' => 92,
                'category_label' => 'Tax & Finance',
                'category_tone' => 'teal',
                'category_icon' => 'fa-calculator',
            ],
            [
                'url' => $indexUrl,
                'image' => 'https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=800&q=80',
                'title' => 'GrowthBridge Consulting',
                'description' => 'Business Strategy & Startup Advisory',
                'location' => 'Patel Nagar, Dehradun',
                'rating_score' => 4.6,
                'rating_count' => 105,
                'category_label' => 'Business Consulting',
                'category_tone' => 'blue',
                'category_icon' => 'fa-briefcase',
            ],
            [
                'url' => $indexUrl,
                'image' => 'https://images.unsplash.com/photo-1521737711867-e3b97375f020?w=800&q=80',
                'title' => 'CareerPath Mentors',
                'description' => 'Career Planning & Interview Coaching',
                'location' => 'Dehradun',
                'rating_score' => 4.4,
                'rating_count' => 86,
                'category_label' => 'Career Guidance',
                'category_tone' => 'purple',
                'category_icon' => 'fa-user-tie',
            ],
            [
                'url' => $indexUrl,
                'image' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=800&q=80',
                'title' => 'Modern Space Architects',
                'description' => 'Architectural design, interior planning & turnkey solutions',
                'location' => 'Dehradun',
                'rating_score' => 4.5,
                'rating_count' => 78,
                'category_label' => 'Architecture',
                'category_tone' => 'orange',
                'category_icon' => 'fa-compass-drafting',
            ],
            [
                'url' => $indexUrl,
                'image' => 'https://images.unsplash.com/photo-1548013146-72479768bada?w=800&q=80',
                'title' => 'Vedic Jyotish & Vastu Solutions',
                'description' => 'Astrology, vastu & life guidance consultations',
                'location' => 'Dehradun',
                'rating_score' => 4.6,
                'rating_count' => 91,
                'category_label' => 'Astrology & Vastu',
                'category_tone' => 'pink',
                'category_icon' => 'fa-om',
            ],
        ];
    }
}
