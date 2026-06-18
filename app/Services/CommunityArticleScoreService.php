<?php

namespace App\Services;

use App\Models\CommunityPost;
use Illuminate\Support\Str;

class CommunityArticleScoreService
{
    /** @var array<string, float> */
    public const WEIGHTS = [
        'views' => 0.20,
        'likes' => 0.20,
        'shares' => 0.15,
        'comments' => 0.15,
        'reading_time' => 0.10,
        'quality_score' => 0.20,
    ];

    /** @var array<string, string> */
    public const BADGE_LABELS = [
        'badge_trending' => 'Trending',
        'badge_editors_choice' => "Editor's Choice",
        'badge_most_read' => 'Most Read',
        'is_featured' => 'Featured',
        'badge_community_pick' => 'Community Pick',
    ];

    public static function recalculate(CommunityPost $post, bool $autoAssignBadges = true): CommunityPost
    {
        $post->loadCount([
            'reactions as likes_count' => fn ($query) => $query->where('reaction', '!=', 'Dislike'),
            'comments',
            'saves',
        ]);

        $breakdown = self::breakdown($post);
        $articleScore = round(collect($breakdown)->sum('weighted'), 2);

        $updates = [
            'article_score' => $articleScore,
            'article_score_calculated_at' => now(),
        ];

        if ($autoAssignBadges) {
            $updates = array_merge($updates, self::suggestedBadges($post, $articleScore, $breakdown));
        }

        $post->update($updates);

        if ($post->content_type === 'stories') {
            CommunityStoryAchievementService::recalculate($post->fresh());
        }

        return $post->fresh();
    }

    /**
     * @return array<string, array{raw: float, normalized: float, weighted: float, max: float}>
     */
    public static function breakdown(CommunityPost $post): array
    {
        $views = (int) $post->views_count;
        $likes = (int) ($post->likes_count ?? $post->reactions()->where('reaction', '!=', 'Dislike')->count());
        $shares = (int) $post->shares_count;
        $comments = (int) ($post->comments_count ?? $post->comments()->count());
        $readingMinutes = self::readingMinutes($post);
        $quality = (float) ($post->quality_score ?? 0);

        return [
            'views' => self::component($views, 250, self::WEIGHTS['views']),
            'likes' => self::component($likes, 50, self::WEIGHTS['likes']),
            'shares' => self::component($shares, 30, self::WEIGHTS['shares']),
            'comments' => self::component($comments, 40, self::WEIGHTS['comments']),
            'reading_time' => self::component(self::readingTimeScore($readingMinutes), 100, self::WEIGHTS['reading_time']),
            'quality_score' => self::component(min(max($quality, 0), 100), 100, self::WEIGHTS['quality_score']),
        ];
    }

    /**
     * @return array<string, bool>
     */
    public static function suggestedBadges(CommunityPost $post, float $articleScore, array $breakdown): array
    {
        $views = (int) $post->views_count;
        $likes = (int) ($post->likes_count ?? 0);
        $comments = (int) ($post->comments_count ?? 0);
        $saves = (int) ($post->saves_count ?? 0);
        $quality = (float) ($post->quality_score ?? 0);
        $publishedRecently = $post->published_at !== null && $post->published_at->greaterThan(now()->subDays(14));
        $mostReadThreshold = max(50, (int) CommunityPost::query()->published()->max('views_count'));

        return [
            'badge_trending' => $publishedRecently && $articleScore >= 55 && ($views >= 20 || $likes >= 5),
            'badge_editors_choice' => $quality >= 75,
            'badge_most_read' => $views >= $mostReadThreshold,
            'badge_community_pick' => ($comments >= 3 && $likes >= 5) || $saves >= 3 || ($comments >= 5 && $articleScore >= 45),
        ];
    }

    /**
     * @return array{raw: float, normalized: float, weighted: float, max: float}
     */
    private static function component(float $raw, float $cap, float $weight): array
    {
        $normalized = $cap > 0 ? min(($raw / $cap) * 100, 100) : 0;

        return [
            'raw' => $raw,
            'normalized' => round($normalized, 2),
            'weighted' => round(($normalized / 100) * ($weight * 100), 2),
            'max' => round($weight * 100, 2),
        ];
    }

    public static function readingMinutes(CommunityPost $post): float
    {
        $metaMinutes = data_get($post->meta, 'reading_time');

        if (is_numeric($metaMinutes) && (float) $metaMinutes > 0) {
            return (float) $metaMinutes;
        }

        $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $post->body)) ?? '');

        if ($text === '') {
            return 0;
        }

        $words = str_word_count($text);

        return max(1, round($words / 200, 1));
    }

    private static function readingTimeScore(float $minutes): float
    {
        if ($minutes <= 0) {
            return 0;
        }

        if ($minutes >= 4 && $minutes <= 10) {
            return 100;
        }

        if ($minutes < 4) {
            return max(35, ($minutes / 4) * 100);
        }

        return max(45, 100 - (($minutes - 10) * 8));
    }

    public static function metricSummary(CommunityPost $post): array
    {
        $post->loadCount([
            'reactions as likes_count' => fn ($query) => $query->where('reaction', '!=', 'Dislike'),
            'comments',
            'saves',
        ]);

        return [
            'views' => (int) $post->views_count,
            'likes' => (int) $post->likes_count,
            'shares' => (int) $post->shares_count,
            'comments' => (int) $post->comments_count,
            'saves' => (int) $post->saves_count,
            'reading_minutes' => self::readingMinutes($post),
            'quality_score' => $post->quality_score,
            'article_score' => (float) $post->article_score,
        ];
    }
}
