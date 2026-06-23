<?php

namespace App\Services;

use App\Mail\CommunityStoryEngagementMail;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CommunitySeniorCitizensForumEngagementNotificationService
{
    /** @var list<string> */
    private const NOTIFY_REACTIONS = [
        'Respect',
        'Inspiring',
        'Valuable Wisdom',
        'Remarkable Journey',
        'Helpful Advice',
    ];

    public static function notifyAuthorOfPublishedPost(CommunityPost $post): void
    {
        if (! $post->isSeniorCitizensForumPost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        if ($post->requiresSeniorCitizensForumPrivateLink()) {
            $privateUrl = $post->seniorCitizensForumPrivateLinkUrl();
            if (filled($privateUrl)) {
                self::notifyAuthor(
                    $post,
                    null,
                    'Your private link is ready',
                    'Private link',
                    'Your Senior Citizens Forum post "'.$post->title.'" is published. Share this private link with readers you trust: '.$privateUrl,
                    route('community.posts.manage', $post)
                );
            }

            return;
        }

        if ((bool) data_get($post->meta, 'senior_citizens_forum_preserve_digital_legacy', false)) {
            self::notifyAuthor(
                $post,
                null,
                'Digital legacy preservation enabled',
                'Digital legacy',
                'Your post "'.$post->title.'" is published and marked for digital legacy preservation — permanent archive, family access, and future PDF/eBook options.',
                route('community.posts.manage', $post)
            );
        }

        $intergenerational = array_values(array_filter((array) data_get($post->meta, 'senior_citizens_forum_intergenerational_connections', [])));
        if ($intergenerational !== []) {
            self::notifyAuthor(
                $post,
                null,
                'Wisdom tags are live',
                'Intergenerational connections',
                'Your post "'.$post->title.'" is tagged for: '.implode(', ', $intergenerational).'. Younger readers can now discover your wisdom.',
                route('community.posts.manage', $post)
            );
        }

        if (filled(data_get($post->meta, 'senior_citizens_forum_ask_community'))) {
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
        if (! $post->isSeniorCitizensForumPost() || ! in_array($reaction, self::NOTIFY_REACTIONS, true)) {
            return;
        }

        self::notifyAuthor(
            $post,
            $reactor,
            'New '.$reaction.' reaction',
            $reaction.' reaction',
            'reacted "'.$reaction.'" to your Senior Citizens Forum post "'.$post->title.'".',
            route('community.posts.manage', $post)
        );
    }

    public static function notifyAuthorOfCommunityResponse(CommunityPost $post, User $responder, string $responseType): void
    {
        if (! $post->isSeniorCitizensForumPost() || ! filled(data_get($post->meta, 'senior_citizens_forum_ask_community'))) {
            return;
        }

        self::notifyAuthor(
            $post,
            $responder,
            'New response to your community question',
            $responseType,
            'responded to your community question on "'.$post->title.'".',
            route('community.posts.manage', $post).'#participation-comments'
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
