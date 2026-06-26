<?php

namespace App\Services;

use App\Mail\CommunityPostParticipationReceivedMail;
use App\Models\CommunityPost;
use App\Models\CommunityReportFollow;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class CommunityCommunityIssuesEngagementNotificationService
{
    public static function maybeNotifyEscalation(CommunityPost $post, int $previousSupportCount): void
    {
        if (! $post->isCommunityIssuesPost() || ! (bool) data_get($post->meta, 'community_issue_allow_campaign', true)) {
            return;
        }

        $post->loadCount('reportSupports');
        $currentCount = (int) $post->report_supports_count;
        $threshold = $post->communityIssueEscalationThreshold();

        if ($currentCount < $threshold || $previousSupportCount >= $threshold) {
            return;
        }

        if ((bool) data_get($post->meta, 'community_issue_escalation_notified', false)) {
            return;
        }

        $meta = is_array($post->meta) ? $post->meta : [];
        $meta['community_issue_escalation_notified'] = true;
        $post->forceFill(['meta' => $meta])->save();

        $post->loadMissing('user');
        $title = 'High priority community issue';
        $message = '"'.$post->title.'" reached '.$currentCount.' supporters (threshold: '.$threshold.'). This issue is now flagged as high priority.';
        $url = route('community.show', $post);

        if ($post->user) {
            PortalNotificationService::notifyUser($post->user, $title, $message, $url, 'community');
            if (filled($post->user->email)) {
                Mail::to($post->user->email)->send(new CommunityPostParticipationReceivedMail(
                    $post,
                    new User(['name' => 'SoilnWater Community']),
                    $title,
                    $message,
                    $url
                ));
            }
        }

        PortalNotificationService::notifyAdmins(
            'Community issue escalated',
            $message,
            $url,
            'community'
        );
    }

    public static function notifyFollowersOfStatusChange(CommunityPost $post, ?string $oldStatus, string $newStatus): void
    {
        if (! $post->isCommunityIssuesPost() || ! $post->allowsCommunityIssueFollow()) {
            return;
        }

        $message = filled($oldStatus)
            ? 'Issue status changed from "'.$oldStatus.'" to "'.$newStatus.'".'
            : 'Issue status updated to "'.$newStatus.'".';

        self::notifyFollowers($post, 'Community issue status update', $message);
    }

    public static function notifyFollowersOfTimelineUpdate(CommunityPost $post): void
    {
        if (! $post->isCommunityIssuesPost() || ! $post->allowsCommunityIssueFollow()) {
            return;
        }

        self::notifyFollowers(
            $post,
            'Resolution timeline updated',
            'The resolution timeline for "'.$post->title.'" has been updated.'
        );
    }

    private static function notifyFollowers(CommunityPost $post, string $title, string $message): void
    {
        $post->loadMissing('user');

        $followers = CommunityReportFollow::query()
            ->with('user')
            ->where('community_post_id', $post->id)
            ->where('user_id', '!=', $post->user_id)
            ->get()
            ->pluck('user')
            ->filter();

        $url = route('community.show', $post);

        foreach ($followers as $follower) {
            PortalNotificationService::notifyUser($follower, $title, $message, $url, 'community');

            if (filled($follower->email)) {
                Mail::to($follower->email)->send(new CommunityPostParticipationReceivedMail(
                    $post,
                    $post->user ?? new User(['name' => 'SoilnWater']),
                    $title,
                    $message,
                    $url
                ));
            }
        }
    }
}
