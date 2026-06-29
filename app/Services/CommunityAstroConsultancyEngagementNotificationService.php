<?php

namespace App\Services;

use App\Mail\CommunityPostParticipationReceivedMail;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CommunityAstroConsultancyEngagementNotificationService
{
    public static function notifyOnPublishedPost(CommunityPost $post): void
    {
        if (! $post->isAstroConsultancyPost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        self::notifyAuthor(
            $post,
            'Your astro consultancy post is live',
            'Your astro consultancy post "'.$post->title.'" is now published on SoilnWater.',
            route('community.posts.manage', $post)
        );

        if (filled(data_get($post->meta, 'astro_consultancy_ask_community'))) {
            self::notifyAskCommunityPublished($post, isNew: true);
        }

        if ($post->astroHasPrivateQueryActions() || $post->astroEnablesLiveQa()) {
            PortalNotificationService::notifyAdmins(
                'New astro consultancy post published',
                'A new astro consultancy post "'.$post->title.'" invites community consultation engagement.',
                route('community.show', $post),
                'community'
            );
        }
    }

    public static function notifyAuthorOfReaction(CommunityPost $post, User $actor, string $reaction): void
    {
        if (! $post->isAstroConsultancyPost() || $actor->id === $post->user_id) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        self::notifyAuthor(
            $post,
            'New astro consultancy reaction',
            ($actor->full_name ?: $actor->name).' reacted "'.$reaction.'" on your astro consultancy post "'.$post->title.'".',
            route('community.posts.manage', $post),
            $actor
        );
    }

    public static function notifyAuthorOfCommunityResponse(CommunityPost $post, User $actor, string $body, bool $isReply): void
    {
        if (! $post->isAstroConsultancyPost() || $actor->id === $post->user_id) {
            return;
        }

        if (! filled(data_get($post->meta, 'astro_consultancy_ask_community'))) {
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

        self::notifyAuthor($post, 'New answer to your community question', $message, route('community.posts.manage', $post), $actor);
    }

    public static function notifyAuthorOfPrivateQuery(CommunityPost $post, User $actor, string $queryType, string $messagePreview): void
    {
        $post->loadMissing('user');

        if (! $post->user || $post->user_id === $actor->id) {
            return;
        }

        $actorName = $actor->full_name ?: $actor->name ?: 'A community member';
        $summary = $actorName.' sent a "'.$queryType.'" request on your astro consultancy post "'.$post->title.'".';
        if (filled($messagePreview)) {
            $summary .= ' Message: '.Str::limit($messagePreview, 180);
        }

        self::notifyAuthor($post, 'New private consultation request', $summary, route('community.posts.manage', $post), $actor);
    }

    public static function notifySubmitterOfPrivateQueryConfirmation(CommunityPost $post, User $actor, string $queryType): void
    {
        $post->loadMissing('user');

        $authorName = $post->user?->full_name ?: $post->user?->name ?: 'the consultant';
        $message = 'Your "'.$queryType.'" request was sent to '.$authorName.' for "'.$post->title.'". They may respond through SoilnWater.';

        PortalNotificationService::notifyUser(
            $actor,
            'Consultation request sent',
            $message,
            route('community.show', $post),
            'community'
        );

        if (filled($actor->email)) {
            Mail::to($actor->email)->send(new CommunityPostParticipationReceivedMail(
                $post,
                $post->user,
                'consultation request confirmation',
                $message,
                route('community.show', $post)
            ));
        }
    }

    public static function notifyAuthorOfGuestPrivateQuery(CommunityPost $post, string $submitterName, string $queryType, string $messagePreview): void
    {
        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        $summary = $submitterName.' sent a "'.$queryType.'" request on your astro consultancy post "'.$post->title.'".';
        if (filled($messagePreview)) {
            $summary .= ' Message: '.Str::limit($messagePreview, 180);
        }

        self::notifyAuthor($post, 'New private consultation request', $summary, route('community.posts.manage', $post));
    }

    public static function maybeNotifyAskCommunityOnUpdate(CommunityPost $post, array $originalMeta): void
    {
        if (! $post->isAstroConsultancyPost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $previousQuestion = trim((string) data_get($originalMeta, 'astro_consultancy_ask_community', ''));
        $currentQuestion = trim((string) data_get($post->meta, 'astro_consultancy_ask_community', ''));

        if (filled($currentQuestion) && $currentQuestion !== $previousQuestion) {
            self::notifyAskCommunityPublished($post, isNew: false);
        }
    }

    private static function notifyAskCommunityPublished(CommunityPost $post, bool $isNew): void
    {
        $question = (string) data_get($post->meta, 'astro_consultancy_ask_community');
        $url = route('community.show', $post);
        $message = ($isNew ? 'A new astro community question was published' : 'An astro community question was updated')
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
            'Astro consultancy community question',
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
