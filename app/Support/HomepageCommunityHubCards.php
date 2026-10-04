<?php

namespace App\Support;

use App\Models\CommunityPost;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class HomepageCommunityHubCards
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
    public static function build(Collection $posts, int $limit = 4): array
    {
        $cards = [];

        foreach ($posts as $post) {
            if (! $post instanceof CommunityPost) {
                continue;
            }

            $cards[] = self::fromPost($post);

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
     * }
     */
    private static function fromPost(CommunityPost $post): array
    {
        $post->loadAvg('starRatings', 'rating');
        $post->loadCount(['starRatings', 'reactions', 'comments']);

        $categoryMeta = self::categoryMetaForType((string) $post->content_type);
        $rating = (float) ($post->star_ratings_avg_rating ?? 0);
        $count = (int) ($post->star_ratings_count ?? 0);
        if ($count === 0) {
            $count = max((int) $post->reactions_count, (int) $post->comments_count, 1);
        }

        $description = filled($post->excerpt)
            ? (string) $post->excerpt
            : Str::limit(strip_tags((string) $post->body), 120);

        $reactions = (int) ($post->reactions_count ?? 0);
        $comments = (int) ($post->comments_count ?? 0);

        return [
            'url' => $post->publicUrl(),
            'image' => $post->featuredImageUrl() ?: $categoryMeta['fallback_image'],
            'title' => $post->title,
            'description' => $description !== '' ? $description : $categoryMeta['description'],
            'location' => self::shortLocation(filled($post->location) ? (string) $post->location : 'Dehradun'),
            'rating_score' => $rating > 0 ? round($rating, 1) : 4.5,
            'rating_count' => $count,
            'category_label' => $categoryMeta['label'],
            'category_tone' => $categoryMeta['tone'],
            'category_icon' => $categoryMeta['icon'],
            'stat_primary' => $comments > 0 ? number_format($comments).'+ Discussions' : '2.4K+ Discussions',
            'stat_secondary' => $reactions > 0 ? number_format($reactions).'+ Reactions' : '8.1K+ Members',
        ];
    }

    private static function shortLocation(string $label): string
    {
        $parts = array_map('trim', explode(',', $label));

        return implode(', ', array_slice($parts, 0, 2));
    }

    /**
     * @return array{label: string, tone: string, icon: string, fallback_image: string, description: string}
     */
    private static function categoryMetaForType(string $contentType): array
    {
        $hubKey = CommunityContentTaxonomy::hubSectionForType($contentType);
        $hubs = CommunityContentTaxonomy::hubSections();

        if ($hubKey !== null && isset($hubs[$hubKey])) {
            $hub = $hubs[$hubKey];

            return [
                'label' => $hub['label'],
                'tone' => self::toneForHub($hubKey),
                'icon' => $hub['icon'] ?? 'fa-book-open',
                'fallback_image' => self::fallbackImageForHub($hubKey),
                'description' => $hub['tagline'] ?? $hub['description'] ?? '',
            ];
        }

        return [
            'label' => CommunityContentTaxonomy::labels()[$contentType] ?? Str::headline($contentType),
            'tone' => 'slate',
            'icon' => 'fa-book-open',
            'fallback_image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&q=80',
            'description' => 'Community stories and local voices',
        ];
    }

    private static function toneForHub(string $hubKey): string
    {
        return match ($hubKey) {
            'knowledge-news' => 'blue',
            'stories-literature' => 'orange',
            'life-learning' => 'teal',
            'environment-agriculture' => 'green',
            'career-business' => 'indigo',
            'culture-spirituality' => 'purple',
            'local-civic' => 'red',
            'creative-community' => 'pink',
            default => 'slate',
        };
    }

    private static function fallbackImageForHub(string $hubKey): string
    {
        return match ($hubKey) {
            'knowledge-news' => 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=800&q=80',
            'stories-literature' => 'https://images.unsplash.com/photo-1481627834876-b7833e8f5570?w=800&q=80',
            'life-learning' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?w=800&q=80',
            'environment-agriculture' => 'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?w=800&q=80',
            'career-business' => 'https://images.unsplash.com/photo-1521737711867-e3b97375f020?w=800&q=80',
            'culture-spirituality' => 'https://images.unsplash.com/photo-1548013146-72479768bada?w=800&q=80',
            'local-civic' => 'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=800&q=80',
            'creative-community' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&q=80',
            default => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=800&q=80',
        };
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
        $hubs = CommunityContentTaxonomy::hubSections();
        $keys = ['knowledge-news', 'stories-literature', 'career-business', 'local-civic', 'life-learning', 'creative-community'];
        $cards = [];

        foreach ($keys as $hubKey) {
            if (! isset($hubs[$hubKey])) {
                continue;
            }

            $hub = $hubs[$hubKey];
            $meta = self::categoryMetaForType($hub['types'][0] ?? $hubKey);

            $cards[] = [
                'url' => route('community.index', ['hub' => $hubKey]),
                'image' => $meta['fallback_image'],
                'title' => $hub['label'],
                'description' => $hub['description'],
                'location' => 'Dehradun',
                'rating_score' => 4.5,
                'rating_count' => 64,
                'category_label' => $hub['label'],
                'category_tone' => $meta['tone'],
                'category_icon' => $meta['icon'],
                'stat_primary' => '1.2K+ Posts',
                'stat_secondary' => '5.6K+ Members',
            ];
        }

        return $cards;
    }
}
