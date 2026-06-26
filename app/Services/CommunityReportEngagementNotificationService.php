<?php

namespace App\Services;

use App\Mail\CommunityPostParticipationReceivedMail;
use App\Models\CommunityPost;
use App\Models\CommunityReportEvidence;
use App\Models\CommunityReportFollow;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class CommunityReportEngagementNotificationService
{
    public static function notifyAuthorOfSupport(CommunityPost $post, User $actor): void
    {
        self::notifyAuthor($post, $actor, self::authorTitle($post, 'supported'), self::authorActionPhrase($post, 'supported'));
    }

    public static function notifyAuthorOfAgreement(CommunityPost $post, User $actor): void
    {
        self::notifyAuthor($post, $actor, self::authorTitle($post, 'agreed'), self::authorActionPhrase($post, 'agreed'));
    }

    public static function notifyAuthorOfFollow(CommunityPost $post, User $actor): void
    {
        self::notifyAuthor($post, $actor, self::authorTitle($post, 'followed'), self::authorActionPhrase($post, 'followed'));
    }

    public static function notifyAuthorOfEvidence(CommunityPost $post, User $actor, int $fileCount): void
    {
        CommunityPostParticipationNotificationService::notifyAuthorOfEvidence($post, $actor, $fileCount);
    }

    public static function notifyFollowersOfReportUpdate(CommunityPost $post, string $updateMessage): void
    {
        if (! $post->supportsCivicEngagement()) {
            return;
        }

        $post->loadMissing('user');

        $followers = CommunityReportFollow::query()
            ->with('user')
            ->where('community_post_id', $post->id)
            ->where('user_id', '!=', $post->user_id)
            ->get()
            ->pluck('user')
            ->filter();

        $subject = match (true) {
            $post->isCommunityIssuesPost() => 'Community issue update',
            $post->isMyAreaPost() => 'My Area update',
            default => 'Report update',
        };
        $noun = match (true) {
            $post->isCommunityIssuesPost() => 'community issue',
            $post->isMyAreaPost() => 'My Area post',
            default => 'report',
        };

        foreach ($followers as $follower) {
            PortalNotificationService::notifyUser(
                $follower,
                $subject,
                'The '.$noun.' "'.$post->title.'" you follow has been updated: '.$updateMessage,
                route('community.show', $post),
                'community'
            );

            $recipient = $follower->email;
            if (filled($recipient)) {
                Mail::to($recipient)->send(new CommunityPostParticipationReceivedMail(
                    $post,
                    $post->user ?? new User(['name' => 'SoilnWater']),
                    $subject,
                    'The '.$noun.' "'.$post->title.'" you follow has been updated: '.$updateMessage,
                    route('community.show', $post)
                ));
            }
        }
    }

    /**
     * @return array{
     *     supports_count: int,
     *     agreements_count: int,
     *     follows_count: int,
     *     evidence_count: int,
     *     user_supported: bool,
     *     user_agreed: bool,
     *     user_following: bool
     * }
     */
    public static function stateForPost(CommunityPost $post, ?int $userId = null): array
    {
        $post->loadCount([
            'reportSupports',
            'reportAgreements',
            'reportFollows',
            'reportEvidence',
        ]);

        $userSupported = false;
        $userAgreed = false;
        $userFollowing = false;

        if ($userId !== null) {
            $userSupported = $post->reportSupports()->where('user_id', $userId)->exists();
            $userAgreed = $post->reportAgreements()->where('user_id', $userId)->exists();
            $userFollowing = $post->reportFollows()->where('user_id', $userId)->exists();
        }

        return [
            'supports_count' => (int) ($post->report_supports_count ?? 0),
            'agreements_count' => (int) ($post->report_agreements_count ?? 0),
            'follows_count' => (int) ($post->report_follows_count ?? 0),
            'evidence_count' => (int) ($post->report_evidence_count ?? 0),
            'user_supported' => $userSupported,
            'user_agreed' => $userAgreed,
            'user_following' => $userFollowing,
        ];
    }

    /**
     * @return Collection<int, CommunityReportEvidence>
     */
    public static function recentEvidence(CommunityPost $post, int $limit = 12): Collection
    {
        return $post->reportEvidence()
            ->with('user:id,name,full_name')
            ->latest()
            ->limit($limit)
            ->get();
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
            route('community.show', $post),
            'community'
        );

        $recipient = $post->user->email;
        if (filled($recipient)) {
            Mail::to($recipient)->send(new CommunityPostParticipationReceivedMail(
                $post,
                $actor,
                $title,
                $message,
                route('community.show', $post)
            ));
        }
    }

    private static function authorTitle(CommunityPost $post, string $action): string
    {
        if ($post->isMyAreaPost()) {
            return match ($action) {
                'supported' => 'My Area support',
                'agreed' => 'Community agreement',
                'followed' => 'Resolution followed',
                default => 'My Area activity',
            };
        }

        if ($post->isCommunityIssuesPost()) {
            return match ($action) {
                'supported' => 'Issue supported',
                'agreed' => 'Issue verified',
                'followed' => 'Issue followed',
                default => 'Community issue activity',
            };
        }

        return match ($action) {
            'supported' => 'Report supported',
            'agreed' => 'Community agreement',
            'followed' => 'Issue followed',
            default => 'Report activity',
        };
    }

    private static function authorActionPhrase(CommunityPost $post, string $action): string
    {
        if ($post->isMyAreaPost()) {
            return match ($action) {
                'supported' => 'supported your My Area post',
                'agreed' => 'agreed with your My Area post',
                'followed' => 'is following your My Area post for resolution updates',
                default => 'engaged with your My Area post',
            };
        }

        if ($post->isCommunityIssuesPost()) {
            return match ($action) {
                'supported' => 'supported your community issue',
                'agreed' => 'verified your community issue',
                'followed' => 'is following your community issue for updates',
                default => 'engaged with your community issue',
            };
        }

        return match ($action) {
            'supported' => 'supported your report',
            'agreed' => 'agreed with your report',
            'followed' => 'is following your report for updates',
            default => 'engaged with your report',
        };
    }
}
