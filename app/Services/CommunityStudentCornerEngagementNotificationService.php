<?php

namespace App\Services;

use App\Mail\CommunityStoryEngagementMail;
use App\Models\CommunityPost;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Support\Facades\Mail;

class CommunityStudentCornerEngagementNotificationService
{
    public static function notifyAuthorOfPublishedPost(CommunityPost $post): void
    {
        if (! $post->isStudentCornerPost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        if ($post->requiresStudentCornerPrivateLink()) {
            $privateUrl = $post->studentCornerPrivateLinkUrl();
            if (filled($privateUrl)) {
                self::notifyAuthor(
                    $post,
                    null,
                    'Your private link is ready',
                    'Private link',
                    'Your Student Corner post "'.$post->title.'" is published. Share this private link with readers you trust: '.$privateUrl,
                    route('community.posts.manage', $post)
                );
            }

            return;
        }

        if ((bool) data_get($post->meta, 'student_corner_submit_to_competition', false)) {
            $categories = array_values(array_filter((array) data_get($post->meta, 'student_corner_competition_categories', [])));
            self::notifyAuthor(
                $post,
                null,
                'Competition entry submitted',
                'Competition entry',
                'Your post "'.$post->title.'" is live'
                    .($categories !== [] ? ' and submitted for: '.implode(', ', $categories) : ' as a competition entry.')
                    .' Moderators may review competition submissions separately.',
                route('community.posts.manage', $post)
            );
        }

        $mentorshipRequests = array_values(array_filter((array) data_get($post->meta, 'student_corner_mentorship_requests', [])));
        if ($mentorshipRequests !== []) {
            self::notifyAuthor(
                $post,
                null,
                'Mentorship requests are live',
                'Mentorship request',
                'Your post "'.$post->title.'" is seeking: '.implode(', ', $mentorshipRequests).'. Fellow students and mentors can respond through comments.',
                route('community.posts.manage', $post).'#participation-comments'
            );
        }

        if (filled(data_get($post->meta, 'student_corner_ask_community'))) {
            self::notifyAuthor(
                $post,
                null,
                'Community question published',
                'Ask the community',
                'Your community question is live on "'.$post->title.'". Watch for responses in your portal.',
                route('community.posts.manage', $post).'#participation-comments'
            );
        }

        if ($post->allowsPoll() && filled(data_get($post->meta, 'student_corner_poll_question'))) {
            self::notifyAuthor(
                $post,
                null,
                'Poll is live',
                'Poll',
                'Your poll "'.data_get($post->meta, 'student_corner_poll_question').'" is now open on "'.$post->title.'".',
                route('community.posts.manage', $post)
            );
        }
    }

    public static function notifyAuthorOfReaction(CommunityPost $post, User $reactor, string $reaction): void
    {
        if (! $post->isStudentCornerPost() || ! in_array($reaction, CommunityContentTaxonomy::studentCornerReactionLabels(), true)) {
            return;
        }

        self::notifyAuthor(
            $post,
            $reactor,
            'New '.$reaction.' reaction',
            $reaction.' reaction',
            'reacted "'.$reaction.'" to your Student Corner post "'.$post->title.'".',
            route('community.posts.manage', $post)
        );
    }

    public static function notifyAuthorOfCommunityResponse(CommunityPost $post, User $responder, string $responseType): void
    {
        if (! $post->isStudentCornerPost() || ! filled(data_get($post->meta, 'student_corner_ask_community'))) {
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
