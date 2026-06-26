<?php

namespace App\Services;

use App\Mail\CommunityPostParticipationReceivedMail;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CommunityAgricultureEngagementNotificationService
{
    public static function notifyOnPublishedPost(CommunityPost $post): void
    {
        if (! $post->isAgriculturePost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        $manageUrl = route('community.posts.manage', $post);
        $publicUrl = route('community.show', $post);

        self::notifyAuthor(
            $post,
            'Your agriculture post is live',
            'Your agriculture post "'.$post->title.'" is now published on SoilnWater.',
            $manageUrl
        );

        if ($post->enablesAgricultureCropDoctor() && $post->agricultureNeedsExpertAssistance()) {
            self::notifyCropDoctorRequest($post, isNew: true);
        }

        if (filled(data_get($post->meta, 'agriculture_ask_community'))) {
            self::notifyAskCommunityPublished($post, isNew: true);
        }
    }

    public static function notifyAuthorOfReaction(CommunityPost $post, User $actor, string $reaction): void
    {
        if (! $post->isAgriculturePost() || $actor->id === $post->user_id) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        self::notifyAuthor(
            $post,
            'New agriculture reaction',
            $actor->full_name ?: $actor->name.' reacted "'.$reaction.'" on your agriculture post "'.$post->title.'".',
            route('community.posts.manage', $post),
            $actor
        );
    }

    public static function notifyAuthorOfCommunityResponse(CommunityPost $post, User $actor, string $body, bool $isReply): void
    {
        if (! $post->isAgriculturePost() || $actor->id === $post->user_id) {
            return;
        }

        if (! $post->enablesAgricultureCropDoctor() && ! filled(data_get($post->meta, 'agriculture_ask_community'))) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        $title = $post->enablesAgricultureCropDoctor()
            ? 'New Crop Doctor response'
            : 'New answer to your agriculture question';

        $message = ($actor->full_name ?: $actor->name)
            .($isReply ? ' replied' : ' commented')
            .' on "'.$post->title.'": "'
            .\Illuminate\Support\Str::limit($body, 180)
            .'"';

        self::notifyAuthor($post, $title, $message, route('community.posts.manage', $post), $actor);
    }

    public static function maybeNotifyCropDoctorRequestOnUpdate(CommunityPost $post, array $originalMeta): void
    {
        if (! $post->isAgriculturePost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $wasEnabled = (bool) data_get($originalMeta, 'agriculture_enable_crop_doctor', false);
        $wasRequested = data_get($originalMeta, 'agriculture_expert_assistance') === 'yes';
        $isEnabled = $post->enablesAgricultureCropDoctor();
        $isRequested = $post->agricultureNeedsExpertAssistance();

        if ($isEnabled && $isRequested && (! $wasEnabled || ! $wasRequested)) {
            self::notifyCropDoctorRequest($post, isNew: false);
        }
    }

    public static function maybeNotifyAskCommunityOnUpdate(CommunityPost $post, array $originalMeta): void
    {
        if (! $post->isAgriculturePost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $previousQuestion = trim((string) data_get($originalMeta, 'agriculture_ask_community', ''));
        $currentQuestion = trim((string) data_get($post->meta, 'agriculture_ask_community', ''));

        if (filled($currentQuestion) && $currentQuestion !== $previousQuestion) {
            self::notifyAskCommunityPublished($post, isNew: false);
        }
    }

    private static function notifyCropDoctorRequest(CommunityPost $post, bool $isNew): void
    {
        $post->loadMissing('user');
        $url = route('community.show', $post);
        $crop = (string) data_get($post->meta, 'agriculture_crop_name', 'crop');
        $problem = (string) data_get($post->meta, 'agriculture_problem_type', 'field issue');
        $message = ($isNew ? 'A new Crop Doctor request was published' : 'A Crop Doctor request was updated')
            .' for "'.$post->title.'" ('.$crop.' · '.$problem.'). Expert assistance is requested.';

        if ($post->user) {
            PortalNotificationService::notifyUser(
                $post->user,
                'Crop Doctor request is live',
                'Your Crop Doctor post is visible to the community. Agronomists and experienced farmers can now respond.',
                route('community.posts.manage', $post),
                'community'
            );
        }

        PortalNotificationService::notifyAdmins(
            'Crop Doctor expert assistance requested',
            $message,
            $url,
            'community'
        );
    }

    private static function notifyAskCommunityPublished(CommunityPost $post, bool $isNew): void
    {
        $question = (string) data_get($post->meta, 'agriculture_ask_community');
        $url = route('community.show', $post);
        $message = ($isNew ? 'A farmer published a new community question' : 'A farmer updated their community question')
            .' on "'.$post->title.'": "'.\Illuminate\Support\Str::limit($question, 180).'"';

        if ($post->user) {
            PortalNotificationService::notifyUser(
                $post->user,
                'Your community question is live',
                'Farmers and experts can now respond to your question on "'.$post->title.'".',
                route('community.posts.manage', $post),
                'community'
            );
        }

        PortalNotificationService::notifyAdmins(
            'Agriculture community question',
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
