<?php

namespace App\Services;

use App\Mail\CommunityPostParticipationReceivedMail;
use App\Models\CommunityPost;
use App\Models\CommunityReportEvidence;
use App\Models\CommunityReportFollow;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CommunityReportEngagementNotificationService
{
    public static function notifyAuthorOfSupport(CommunityPost $post, User $actor): void
    {
        self::notifyAuthor($post, $actor, 'Report supported', 'supported your report');
    }

    public static function notifyAuthorOfAgreement(CommunityPost $post, User $actor): void
    {
        self::notifyAuthor($post, $actor, 'Community agreement', 'agreed with your report');
    }

    public static function notifyAuthorOfFollow(CommunityPost $post, User $actor): void
    {
        self::notifyAuthor($post, $actor, 'Issue followed', 'is following your report for updates');
    }

    public static function notifyAuthorOfEvidence(CommunityPost $post, User $actor, int $fileCount): void
    {
        CommunityPostParticipationNotificationService::notifyAuthorOfEvidence($post, $actor, $fileCount);
    }

    public static function notifyFollowersOfReportUpdate(CommunityPost $post, string $updateMessage): void
    {
        if (! $post->isReportContent()) {
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

        foreach ($followers as $follower) {
            PortalNotificationService::notifyUser(
                $follower,
                'Report update',
                'The report "'.$post->title.'" you follow has been updated: '.$updateMessage,
                route('community.show', $post),
                'community'
            );
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
}
