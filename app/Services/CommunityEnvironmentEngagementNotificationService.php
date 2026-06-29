<?php

namespace App\Services;

use App\Mail\CommunityPostParticipationReceivedMail;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CommunityEnvironmentEngagementNotificationService
{
    public static function notifyOnPublishedPost(CommunityPost $post): void
    {
        if (! $post->isEnvironmentPost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        self::notifyAuthor(
            $post,
            'Your environment post is live',
            'Your environment post "'.$post->title.'" is now published on SoilnWater.',
            route('community.posts.manage', $post)
        );

        if (filled(data_get($post->meta, 'environment_ask_community'))) {
            self::notifyAskCommunityPublished($post, isNew: true);
        }

        if ($post->environmentHasParticipationActions()) {
            PortalNotificationService::notifyAdmins(
                'New environment campaign published',
                'A new environment post "'.$post->title.'" invites community participation.',
                route('community.show', $post),
                'community'
            );
        }
    }

    public static function notifyAuthorOfReaction(CommunityPost $post, User $actor, string $reaction): void
    {
        if (! $post->isEnvironmentPost() || $actor->id === $post->user_id) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        self::notifyAuthor(
            $post,
            'New environment reaction',
            ($actor->full_name ?: $actor->name).' reacted "'.$reaction.'" on your environment post "'.$post->title.'".',
            route('community.posts.manage', $post),
            $actor
        );
    }

    public static function notifyAuthorOfCommunityResponse(CommunityPost $post, User $actor, string $body, bool $isReply): void
    {
        if (! $post->isEnvironmentPost() || $actor->id === $post->user_id) {
            return;
        }

        if (! filled(data_get($post->meta, 'environment_ask_community'))) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        $message = ($actor->full_name ?: $actor->name)
            .($isReply ? ' replied' : ' commented')
            .' on "'.$post->title.'": "'
            .Str::limit($body, 180)
            .'"';

        self::notifyAuthor($post, 'New answer to your environment question', $message, route('community.posts.manage', $post), $actor);
    }

    public static function notifyAuthorOfSupport(CommunityPost $post, User $actor): void
    {
        self::notifyParticipation($post, $actor, 'Initiative supported', 'supported your environment initiative');
    }

    public static function notifyAuthorOfFollow(CommunityPost $post, User $actor): void
    {
        self::notifyParticipation($post, $actor, 'New campaign follower', 'is now following your environment campaign');
    }

    public static function notifyAuthorOfVolunteer(CommunityPost $post, string $volunteerName, ?User $actor = null, ?string $interest = null): void
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
        $message = $actorName.' registered as a volunteer for "'.$post->title.'"'
            .(filled($interest) ? ' ('.$interest.')' : '')
            .'.';

        PortalNotificationService::notifyUser(
            $post->user,
            'New environment volunteer',
            $message,
            route('community.posts.manage', $post),
            'community'
        );

        if (filled($post->user->email)) {
            Mail::to($post->user->email)->send(new CommunityPostParticipationReceivedMail(
                $post,
                $actor,
                'New environment volunteer',
                $message,
                route('community.posts.manage', $post)
            ));
        }
    }

    public static function maybeNotifyAskCommunityOnUpdate(CommunityPost $post, array $originalMeta): void
    {
        if (! $post->isEnvironmentPost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $previousQuestion = trim((string) data_get($originalMeta, 'environment_ask_community', ''));
        $currentQuestion = trim((string) data_get($post->meta, 'environment_ask_community', ''));

        if (filled($currentQuestion) && $currentQuestion !== $previousQuestion) {
            self::notifyAskCommunityPublished($post, isNew: false);
        }
    }

    private static function notifyAskCommunityPublished(CommunityPost $post, bool $isNew): void
    {
        $question = (string) data_get($post->meta, 'environment_ask_community');
        $url = route('community.show', $post);
        $message = ($isNew ? 'A new environment community question was published' : 'An environment community question was updated')
            .' on "'.$post->title.'": "'.Str::limit($question, 180).'"';

        if ($post->user) {
            PortalNotificationService::notifyUser(
                $post->user,
                'Your community question is live',
                'Community members can now respond to your question on "'.$post->title.'".',
                route('community.posts.manage', $post),
                'community'
            );
        }

        PortalNotificationService::notifyAdmins(
            'Environment community question',
            $message,
            $url,
            'community'
        );
    }

    private static function notifyParticipation(CommunityPost $post, User $actor, string $title, string $actionPhrase): void
    {
        $post->loadMissing('user');

        if (! $post->user || $post->user_id === $actor->id) {
            return;
        }

        $actorName = $actor->full_name ?: $actor->name ?: 'A community member';
        $message = $actorName.' '.$actionPhrase.' "'.$post->title.'".';

        self::notifyAuthor($post, $title, $message, route('community.posts.manage', $post), $actor);
    }

    private static function notifyAuthor(
        CommunityPost $post,
        string $title,
        string $message,
        string $url,
        ?User $actor = null
    ): void {
        if (! $post->user) {
            return;
        }

        PortalNotificationService::notifyUser($post->user, $title, $message, $url, 'community');

        if (filled($post->user->email)) {
            Mail::to($post->user->email)->send(new CommunityPostParticipationReceivedMail(
                $post,
                $actor ?? new User(['name' => 'SoilnWater Community']),
                $title,
                $message,
                $url
            ));
        }
    }
}
