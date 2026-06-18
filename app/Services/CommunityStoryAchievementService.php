<?php

namespace App\Services;

use App\Models\CommunityPost;

class CommunityStoryAchievementService
{
    /** @var array<string, string> */
    public const BADGE_LABELS = [
        'badge_most_read' => 'Most Read',
        'badge_most_shared' => 'Most Shared',
        'badge_most_inspiring' => 'Most Inspiring',
        'badge_community_favorite' => 'Community Favorite',
    ];

    public static function recalculate(CommunityPost $post, bool $notifyAuthor = true): CommunityPost
    {
        if ($post->content_type !== 'stories') {
            return $post;
        }

        $previousBadges = self::currentBadgeStates($post);

        $post->loadCount([
            'reactions as inspiring_count' => fn ($query) => $query->where('reaction', 'Inspiring'),
            'saves',
            'starRatings',
        ]);

        $views = (int) $post->views_count;
        $shares = (int) $post->shares_count;
        $inspiring = (int) ($post->inspiring_count ?? 0);
        $saves = (int) ($post->saves_count ?? 0);
        $ratingCount = (int) ($post->star_ratings_count ?? 0);
        $averageRating = $post->averageStarRating();

        $mostReadThreshold = max(50, (int) CommunityPost::query()
            ->published()
            ->where('content_type', 'stories')
            ->max('views_count'));

        $mostSharedThreshold = max(10, (int) CommunityPost::query()
            ->published()
            ->where('content_type', 'stories')
            ->max('shares_count'));

        $post->update([
            'badge_most_read' => $views >= $mostReadThreshold,
            'badge_most_shared' => $shares >= $mostSharedThreshold && $shares > 0,
            'badge_most_inspiring' => $inspiring >= 5 || ($ratingCount >= 3 && $averageRating !== null && $averageRating >= 4.5),
            'badge_community_favorite' => ($saves >= 3 && $averageRating !== null && $averageRating >= 4.0)
                || ($ratingCount >= 5 && $averageRating !== null && $averageRating >= 4.2)
                || ((float) $post->article_score >= 55 && $saves >= 2),
        ]);

        $post = $post->fresh();
        $newBadgeLabels = self::detectNewBadges($previousBadges, $post);

        if ($notifyAuthor && $newBadgeLabels !== []) {
            CommunityStoryEngagementNotificationService::notifyAuthorOfNewBadges($post, $newBadgeLabels);
        }

        return $post;
    }

    /**
     * @return array<string, bool>
     */
    public static function currentBadgeStates(CommunityPost $post): array
    {
        $states = [];

        foreach (array_keys(self::BADGE_LABELS) as $field) {
            $states[$field] = (bool) $post->{$field};
        }

        return $states;
    }

    /**
     * @param  array<string, bool>  $previousBadges
     * @return list<string>
     */
    public static function detectNewBadges(array $previousBadges, CommunityPost $post): array
    {
        $labels = [];

        foreach (self::BADGE_LABELS as $field => $label) {
            if (! ($previousBadges[$field] ?? false) && (bool) $post->{$field}) {
                $labels[] = $label;
            }
        }

        return $labels;
    }

    /**
     * @return list<array{label: string, class: string}>
     */
    public static function badgesFor(CommunityPost $post): array
    {
        if ($post->content_type !== 'stories') {
            return [];
        }

        $badges = [];

        foreach (self::BADGE_LABELS as $field => $label) {
            if ((bool) $post->{$field}) {
                $badges[] = [
                    'label' => $label,
                    'class' => 'community-story-badge--'.str_replace('_', '-', str_replace('badge_', '', $field)),
                ];
            }
        }

        return $badges;
    }
}

