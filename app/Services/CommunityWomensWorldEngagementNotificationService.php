<?php

namespace App\Services;

use App\Mail\CommunityStoryEngagementMail;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CommunityWomensWorldEngagementNotificationService
{
    /** @var list<string> */
    private const NOTIFY_REACTIONS = [
        'Inspiring',
        'Empowering',
        'Helpful',
        'Strong',
        'Respect',
        'Excellent',
    ];

    public static function notifyAuthorOfPublishedPost(CommunityPost $post): void
    {
        if (! $post->isWomensWorldPost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        if ($post->requiresWomensWorldPrivateLink()) {
            $privateUrl = $post->womensWorldPrivateLinkUrl();
            if (filled($privateUrl)) {
                self::notifyAuthor(
                    $post,
                    null,
                    'Your private link is ready',
                    'Private link',
                    'Your Women\'s World post "'.$post->title.'" is published. Share this private link with readers you trust: '.$privateUrl,
                    route('community.posts.manage', $post)
                );
            }

            return;
        }

        $supportRequests = array_values(array_filter((array) data_get($post->meta, 'womens_world_support_requests', [])));
        if ($supportRequests !== []) {
            self::notifyAuthor(
                $post,
                null,
                'Support request is live',
                'Support request',
                'Your post "'.$post->title.'" is live and marked as seeking: '.implode(', ', $supportRequests).'. Readers can respond through comments and questions.',
                route('community.posts.manage', $post)
            );
        }

        if (filled(data_get($post->meta, 'womens_world_ask_community'))) {
            self::notifyAuthor(
                $post,
                null,
                'Community question published',
                'Ask the community',
                'Your community question is live on "'.$post->title.'". Watch for responses in your portal.',
                route('community.posts.manage', $post).'#participation-comments'
            );
        }
    }

    public static function notifyAuthorOfReaction(CommunityPost $post, User $reactor, string $reaction): void
    {
        if (! $post->isWomensWorldPost() || ! in_array($reaction, self::NOTIFY_REACTIONS, true)) {
            return;
        }

        self::notifyAuthor(
            $post,
            $reactor,
            'New '.$reaction.' reaction',
            $reaction.' reaction',
            'reacted "'.$reaction.'" to your Women\'s World post "'.$post->title.'".',
            route('community.posts.manage', $post)
        );
    }

    private static function notifyAuthor(
        CommunityPost $post,
        ?User $actor,
        string $title,
        string $engagementType,
        string $summary,
        ?string $url = null,
    ): void {
        $post->loadMissing('user');

        if (! $post->user || ($actor && $post->user_id === $actor->id)) {
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
            $url
        ));
    }
}
