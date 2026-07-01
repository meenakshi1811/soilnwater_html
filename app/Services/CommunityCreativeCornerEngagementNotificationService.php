<?php

namespace App\Services;

use App\Mail\CommunityPostParticipationReceivedMail;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CommunityCreativeCornerEngagementNotificationService
{
    public static function notifyOnPublishedPost(CommunityPost $post): void
    {
        if (! $post->isCreativeCornerPost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        self::notifyAuthor(
            $post,
            'Your Creative Corner post is live',
            'Your creative work "'.$post->title.'" is now published on SoilnWater.',
            route('community.posts.manage', $post)
        );

        if (filled(data_get($post->meta, 'creative_corner_ask_community'))) {
            self::notifyAskCommunityPublished($post, isNew: true);
        }

        if ($post->creativeCornerHasCommerceFeatures()) {
            PortalNotificationService::notifyAdmins(
                'Creative work listed for sale',
                'A Creative Corner post "'.$post->title.'" is available for sale or commission on SoilnWater.',
                route('community.show', $post),
                'community'
            );
        }

        if (data_get($post->meta, 'creative_corner_submit_to_competition')) {
            PortalNotificationService::notifyAdmins(
                'Creative competition entry submitted',
                'A Creative Corner post "'.$post->title.'" was submitted for a creative competition.',
                route('community.show', $post),
                'community'
            );
        }
    }

    public static function notifyAuthorOfReaction(CommunityPost $post, User $actor, string $reaction): void
    {
        if (! $post->isCreativeCornerPost() || $actor->id === $post->user_id) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        self::notifyAuthor(
            $post,
            'New Creative Corner reaction',
            ($actor->full_name ?: $actor->name).' reacted "'.$reaction.'" on your creative work "'.$post->title.'".',
            route('community.posts.manage', $post),
            $actor
        );
    }

    public static function notifyAuthorOfCommunityResponse(CommunityPost $post, User $actor, string $body, bool $isReply): void
    {
        if (! $post->isCreativeCornerPost() || $actor->id === $post->user_id) {
            return;
        }

        if (! filled(data_get($post->meta, 'creative_corner_ask_community'))) {
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

        self::notifyAuthor($post, 'New response to your creative question', $message, route('community.posts.manage', $post), $actor);
    }

    public static function maybeNotifyAskCommunityOnUpdate(CommunityPost $post, array $originalMeta): void
    {
        if (! $post->isCreativeCornerPost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $previousQuestion = trim((string) data_get($originalMeta, 'creative_corner_ask_community', ''));
        $currentQuestion = trim((string) data_get($post->meta, 'creative_corner_ask_community', ''));

        if (filled($currentQuestion) && $currentQuestion !== $previousQuestion) {
            self::notifyAskCommunityPublished($post, isNew: false);
        }
    }

    private static function notifyAskCommunityPublished(CommunityPost $post, bool $isNew): void
    {
        $question = (string) data_get($post->meta, 'creative_corner_ask_community');
        $url = route('community.show', $post);
        $message = ($isNew ? 'A new Creative Corner community question was published' : 'A Creative Corner community question was updated')
            .' on "'.$post->title.'": "'.Str::limit($question, 180).'"';

        if ($post->user) {
            PortalNotificationService::notifyUser(
                $post->user,
                'Your creative question is live',
                'Community members can now respond to your question on "'.$post->title.'".',
                route('community.posts.manage', $post),
                'community'
            );
        }

        PortalNotificationService::notifyAdmins(
            'Creative Corner community question',
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
