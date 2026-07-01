<?php

namespace App\Services;

use App\Mail\CommunityPostParticipationReceivedMail;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CommunityCompetitionsEngagementNotificationService
{
    public static function notifyOnPublishedPost(CommunityPost $post): void
    {
        if (! $post->isCompetitionsPost() || ! $post->isPubliclyVisible()) {
            return;
        }

        $post->loadMissing('user');

        if (! $post->user) {
            return;
        }

        self::notifyAuthor(
            $post,
            'Your competition listing is live',
            'Your competition "'.$post->title.'" is now published on SoilnWater. Participants can view rules, dates, and registration details.',
            route('community.posts.manage', $post)
        );

        if ($post->competitionsHasFlagshipFeatures()) {
            PortalNotificationService::notifyAdmins(
                'New competition with SoilnWater flagship features',
                'Competition "'.$post->title.'" includes SoilnWater competition engine features such as leaderboards, badges, or digital certificates.',
                route('community.show', $post),
                'community'
            );
        }

        if (data_get($post->meta, 'competitions_enable_sponsored_branding')) {
            PortalNotificationService::notifyAdmins(
                'Sponsored competition published',
                'A sponsored competition "'.$post->title.'" is now live on SoilnWater.',
                route('community.show', $post),
                'community'
            );
        }

        if (data_get($post->meta, 'competitions_registration_required')) {
            PortalNotificationService::notifyAdmins(
                'Competition registration open',
                'Competition "'.$post->title.'" requires participant registration. Review organizer details and submission deadlines.',
                route('community.show', $post),
                'community'
            );
        }

        if (in_array(data_get($post->meta, 'competitions_voting_system'), ['Public Voting', 'Judges + Public'], true)) {
            PortalNotificationService::notifyAdmins(
                'Public voting competition published',
                'Competition "'.$post->title.'" enables public voting. Fraud protection settings should be monitored during the voting period.',
                route('community.show', $post),
                'community'
            );
        }
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
