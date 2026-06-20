<?php

namespace App\Services;

use App\Mail\CommunityPostParticipationReceivedMail;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CommunityAwarenessEngagementNotificationService
{
    public static function notifyAuthorOfSupport(CommunityPost $post, User $actor): void
    {
        self::notifyAuthor($post, $actor, 'Cause supported', 'supported your awareness campaign');
    }

    public static function notifyAuthorOfPledge(CommunityPost $post, User $actor, string $pledgeText): void
    {
        self::notifyAuthor(
            $post,
            $actor,
            'Campaign pledge received',
            'pledged "'.$pledgeText.'" on your awareness campaign'
        );
    }

    public static function notifyAuthorOfVolunteer(CommunityPost $post, string $volunteerName, ?User $actor = null): void
    {
        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        if ($actor !== null && $post->user_id === $actor->id) {
            return;
        }

        $actorName = $actor
            ? ($actor->full_name ?: $actor->name ?: 'A community member')
            : $volunteerName;
        $message = $actorName.' joined your awareness campaign "'.$post->title.'" as a volunteer.';

        PortalNotificationService::notifyUser(
            $post->user,
            'New campaign volunteer',
            $message,
            route('community.posts.manage', $post),
            'community'
        );

        $recipient = $post->user->email;
        if (filled($recipient)) {
            Mail::to($recipient)->send(new CommunityPostParticipationReceivedMail(
                $post,
                $actor,
                'New campaign volunteer',
                $message,
                route('community.posts.manage', $post)
            ));
        }
    }

    private static function notifyAuthor(CommunityPost $post, User $actor, string $title, string $actionPhrase): void
    {
        $post->loadMissing('user');

        if (! $post->user || $post->user_id === $actor->id) {
            return;
        }

        $actorName = $actor->full_name ?: $actor->name ?: 'A community member';
        $message = $actorName.' '.$actionPhrase.' "'.$post->title.'".';

        PortalNotificationService::notifyUser(
            $post->user,
            $title,
            $message,
            route('community.posts.manage', $post),
            'community'
        );

        $recipient = $post->user->email;
        if (filled($recipient)) {
            Mail::to($recipient)->send(new CommunityPostParticipationReceivedMail(
                $post,
                $actor,
                $title,
                $message,
                route('community.posts.manage', $post)
            ));
        }
    }
}
