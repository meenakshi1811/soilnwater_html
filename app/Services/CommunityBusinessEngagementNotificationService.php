<?php

namespace App\Services;

use App\Mail\CommunityPostParticipationReceivedMail;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CommunityBusinessEngagementNotificationService
{
    public static function notifyAuthorOfQuery(CommunityPost $post, User $actor, string $queryType, string $messagePreview): void
    {
        $post->loadMissing('user');

        if (! $post->user || $post->user_id === $actor->id) {
            return;
        }

        $actorName = $actor->full_name ?: $actor->name ?: 'A community member';
        $summary = $actorName.' sent a "'.$queryType.'" message on your business post "'.$post->title.'".';
        if (filled($messagePreview)) {
            $summary .= ' Message: '.\Illuminate\Support\Str::limit($messagePreview, 180);
        }

        PortalNotificationService::notifyUser(
            $post->user,
            'New business inquiry',
            $summary,
            route('community.posts.manage', $post),
            'community'
        );

        $recipient = $post->user->email;
        if (filled($recipient)) {
            Mail::to($recipient)->send(new CommunityPostParticipationReceivedMail(
                $post,
                $actor,
                'business inquiry',
                $summary,
                route('community.posts.manage', $post)
            ));
        }
    }

    public static function notifySubmitterOfQueryConfirmation(CommunityPost $post, User $actor, string $queryType): void
    {
        $post->loadMissing('user');

        $authorName = $post->user?->full_name ?: $post->user?->name ?: 'the author';
        $message = 'Your "'.$queryType.'" message was sent to '.$authorName.' for "'.$post->title.'". They may respond through the community.';

        PortalNotificationService::notifyUser(
            $actor,
            'Business inquiry sent',
            $message,
            route('community.show', $post),
            'community'
        );

        $recipient = $actor->email;
        if (filled($recipient)) {
            Mail::to($recipient)->send(new CommunityPostParticipationReceivedMail(
                $post,
                $post->user,
                'business inquiry confirmation',
                $message,
                route('community.show', $post)
            ));
        }
    }

    public static function notifyAuthorOfGuestQuery(CommunityPost $post, string $submitterName, string $queryType, string $messagePreview): void
    {
        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        $summary = $submitterName.' sent a "'.$queryType.'" message on your business post "'.$post->title.'".';
        if (filled($messagePreview)) {
            $summary .= ' Message: '.\Illuminate\Support\Str::limit($messagePreview, 180);
        }

        PortalNotificationService::notifyUser(
            $post->user,
            'New business inquiry',
            $summary,
            route('community.posts.manage', $post),
            'community'
        );

        $recipient = $post->user->email;
        if (filled($recipient)) {
            Mail::to($recipient)->send(new CommunityPostParticipationReceivedMail(
                $post,
                null,
                'business inquiry',
                $summary,
                route('community.posts.manage', $post)
            ));
        }
    }
}
