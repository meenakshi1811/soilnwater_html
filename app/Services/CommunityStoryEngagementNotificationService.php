<?php

namespace App\Services;

use App\Mail\CommunityStoryEngagementMail;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CommunityStoryEngagementNotificationService
{
    /**
     * @param  list<string>  $badgeLabels
     */
    public static function notifyAuthorOfNewBadges(CommunityPost $post, array $badgeLabels): void
    {
        if ($badgeLabels === []) {
            return;
        }

        $labelText = count($badgeLabels) === 1
            ? $badgeLabels[0]
            : implode(', ', $badgeLabels);

        self::notifyAuthor(
            $post,
            null,
            count($badgeLabels) === 1 ? 'Story achievement earned' : 'Story achievements earned',
            'Achievement',
            'Your story "'.$post->title.'" earned: '.$labelText.'.',
            route('community.posts.manage', $post),
            $badgeLabels,
            null
        );
    }

    public static function notifyAuthorOfRating(CommunityPost $post, User $rater, int $rating): void
    {
        $contentLabel = self::contentLabel($post);

        self::notifyAuthor(
            $post,
            $rater,
            'New '.$contentLabel.' rating',
            'Rating',
            'rated your '.$contentLabel.' "'.$post->title.'" '.$rating.' star'.($rating === 1 ? '' : 's').'.',
            route('community.posts.manage', $post),
            null,
            $rating
        );
    }

    public static function notifyAuthorOfInspiringReaction(CommunityPost $post, User $reactor): void
    {
        $contentLabel = self::contentLabel($post);

        self::notifyAuthor(
            $post,
            $reactor,
            ucfirst($contentLabel).' marked inspiring',
            'Inspiring reaction',
            'found your '.$contentLabel.' "'.$post->title.'" inspiring.',
            route('community.posts.manage', $post),
            null,
            null
        );
    }

    public static function notifyAuthorOfPublishedWithoutAudio(CommunityPost $post): void
    {
        if ($post->content_type === 'poetry' && ! $post->poetryAudioUrl()) {
            self::notifyAuthor(
                $post,
                null,
                'Add an audio recitation',
                'Audio tip',
                'Readers love hearing poetry aloud. Consider adding an MP3 or voice recording to "'.$post->title.'".',
                route('community.posts.edit', $post),
                null,
                null
            );

            return;
        }

        if ($post->content_type === 'autobiography') {
            $missingAudio = ! $post->autobiographyAudioUrl();
            $missingTimeline = count((array) data_get($post->meta, 'life_timeline', [])) === 0;

            if (! $missingAudio && ! $missingTimeline) {
                return;
            }

            $tips = [];
            if ($missingTimeline) {
                $tips[] = 'a life timeline with key milestones';
            }
            if ($missingAudio) {
                $tips[] = 'audio memories (MP3 upload or voice recording)';
            }

            $tipText = count($tips) === 1
                ? $tips[0]
                : implode(' and ', [implode(', ', array_slice($tips, 0, -1)), end($tips)]);

            self::notifyAuthor(
                $post,
                null,
                'Enhance your autobiography',
                'Content tip',
                'Your autobiography "'.$post->title.'" is live. Readers connect deeply when stories include '.$tipText.'.',
                route('community.posts.edit', $post),
                null,
                null
            );
        }
    }

    private static function contentLabel(CommunityPost $post): string
    {
        return match ($post->content_type) {
            'poetry' => 'poem',
            'stories' => 'story',
            'autobiography' => 'autobiography',
            default => 'post',
        };
    }

    /**
     * @param  list<string>|null  $badgeLabels
     */
    private static function notifyAuthor(
        CommunityPost $post,
        ?User $actor,
        string $title,
        string $engagementType,
        string $summary,
        ?string $url = null,
        ?array $badgeLabels = null,
        ?int $rating = null,
    ): void {
        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        if ($actor && $post->user_id === $actor->id) {
            return;
        }

        $actorName = $actor
            ? ($actor->full_name ?: $actor->name ?: 'A community member')
            : 'SoilnWater Community';

        $message = $actor
            ? $actorName.' '.$summary
            : $summary;

        PortalNotificationService::notifyUser(
            $post->user,
            $title,
            $message,
            $url ?? route('community.posts.manage', $post),
            'community'
        );

        $recipient = $post->user->email;
        if (! filled($recipient)) {
            return;
        }

        Mail::to($recipient)->send(new CommunityStoryEngagementMail(
            $post,
            $actor,
            $engagementType,
            $summary,
            $url,
            $badgeLabels,
            $rating
        ));
    }
}
