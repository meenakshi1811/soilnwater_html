<?php

namespace App\Services;

use App\Mail\CommunityPostParticipationReceivedMail;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CommunityReligionSpiritualityEngagementNotificationService
{
    public static function notifyOnPublishedPost(CommunityPost $post): void
    {
        if (! $post->isReligionSpiritualityPost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        self::notifyAuthor(
            $post,
            'Your Religion & Spirituality post is live',
            'Your Religion & Spirituality post "'.$post->title.'" is now published on SoilnWater.',
            route('community.posts.manage', $post)
        );

        if (filled(data_get($post->meta, 'religion_spirituality_ask_community'))) {
            self::notifyAskCommunityPublished($post, isNew: true);
        }

        if ($post->religionSpiritualityHasFlagshipFeatures()) {
            PortalNotificationService::notifyAdmins(
                'New Religion & Spirituality post published',
                'A new Religion & Spirituality post "'.$post->title.'" includes SoilnWater flagship programs.',
                route('community.show', $post),
                'community'
            );
        }
    }

    public static function notifyAuthorOfReaction(CommunityPost $post, User $actor, string $reaction): void
    {
        if (! $post->isReligionSpiritualityPost() || $actor->id === $post->user_id) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        self::notifyAuthor(
            $post,
            'New Religion & Spirituality reaction',
            ($actor->full_name ?: $actor->name).' reacted "'.$reaction.'" on your Religion & Spirituality post "'.$post->title.'".',
            route('community.posts.manage', $post),
            $actor
        );
    }

    public static function notifyAuthorOfCommunityResponse(CommunityPost $post, User $actor, string $body, bool $isReply): void
    {
        if (! $post->isReligionSpiritualityPost() || $actor->id === $post->user_id) {
            return;
        }

        if (! filled(data_get($post->meta, 'religion_spirituality_ask_community'))) {
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

        self::notifyAuthor($post, 'New response to your community question', $message, route('community.posts.manage', $post), $actor);
    }

    public static function maybeNotifyAskCommunityOnUpdate(CommunityPost $post, array $originalMeta): void
    {
        if (! $post->isReligionSpiritualityPost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $previousQuestion = trim((string) data_get($originalMeta, 'religion_spirituality_ask_community', ''));
        $currentQuestion = trim((string) data_get($post->meta, 'religion_spirituality_ask_community', ''));

        if (filled($currentQuestion) && $currentQuestion !== $previousQuestion) {
            self::notifyAskCommunityPublished($post, isNew: false);
        }
    }

    private static function notifyAskCommunityPublished(CommunityPost $post, bool $isNew): void
    {
        $question = (string) data_get($post->meta, 'religion_spirituality_ask_community');
        $url = route('community.show', $post);
        $message = ($isNew ? 'A new Religion & Spirituality community question was published' : 'A Religion & Spirituality community question was updated')
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
            'Religion & Spirituality community question',
            $message,
            $url,
            'community'
        );
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
