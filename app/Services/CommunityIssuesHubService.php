<?php

namespace App\Services;

use App\Models\CommunityPost;
use App\Models\CommunityReportAgreement;
use App\Models\CommunityReportSupport;
use App\Models\User;
use App\Support\CommunityContentTaxonomy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CommunityIssuesHubService
{
    /**
     * @return array{
     *     total_reported: int,
     *     issues_resolved: int,
     *     issues_under_review: int,
     *     community_support_count: int,
     *     authority_response_rate: float,
     *     authority_responded: int,
     *     authority_eligible: int,
     * }
     */
    public function dashboardStats(?User $viewer = null): array
    {
        $baseQuery = $this->publishedIssuesQuery($viewer);
        $totalReported = (clone $baseQuery)->count();

        $issuesResolved = (clone $baseQuery)
            ->whereIn('meta->community_issue_status_tracker', CommunityContentTaxonomy::communityIssueResolvedStatuses())
            ->count();

        $issuesUnderReview = (clone $baseQuery)
            ->where(function (Builder $builder): void {
                $builder
                    ->whereIn('meta->community_issue_status_tracker', CommunityContentTaxonomy::communityIssueUnderReviewStatuses())
                    ->orWhereNull('meta->community_issue_status_tracker')
                    ->orWhere('meta->community_issue_status_tracker', 'Reported');
            })
            ->count();

        $issueIds = (clone $baseQuery)->pluck('id');

        $communitySupportCount = $issueIds->isEmpty()
            ? 0
            : CommunityReportSupport::query()->whereIn('community_post_id', $issueIds)->count();

        $authorityResponded = (clone $baseQuery)
            ->whereIn('meta->community_issue_status_tracker', CommunityContentTaxonomy::communityIssueAuthorityRespondedStatuses())
            ->count();

        $authorityEligible = (clone $baseQuery)
            ->whereIn('meta->community_issue_status_tracker', CommunityContentTaxonomy::communityIssueAuthorityEligibleStatuses())
            ->count();

        $authorityResponseRate = $authorityEligible > 0
            ? round(($authorityResponded / $authorityEligible) * 100, 1)
            : 0.0;

        return [
            'total_reported' => $totalReported,
            'issues_resolved' => $issuesResolved,
            'issues_under_review' => $issuesUnderReview,
            'community_support_count' => $communitySupportCount,
            'authority_response_rate' => $authorityResponseRate,
            'authority_responded' => $authorityResponded,
            'authority_eligible' => $authorityEligible,
        ];
    }

    /**
     * @return list<array{lat: float, lng: float, intensity: float, title: string, url: string, category: ?string, severity: ?string, status: ?string}>
     */
    public function heatMapMarkers(string $preset = 'all', ?User $viewer = null): array
    {
        $presets = CommunityContentTaxonomy::communityIssueHeatMapPresets();
        $presetConfig = $presets[$preset] ?? $presets['all'];
        $categories = $presetConfig['categories'];

        $query = $this->publishedIssuesQuery($viewer)
            ->whereNotNull('location_lat')
            ->whereNotNull('location_lng');

        if ($categories !== []) {
            $query->where(function (Builder $builder) use ($categories): void {
                foreach ($categories as $category) {
                    $builder->orWhere('meta->community_issue_category', $category);
                }
            });
        }

        return $query
            ->latest('published_at')
            ->limit(500)
            ->get(['id', 'slug', 'title', 'location_lat', 'location_lng', 'meta'])
            ->map(function (CommunityPost $post): array {
                $severity = (string) data_get($post->meta, 'community_issue_severity', '');

                return [
                    'lat' => (float) $post->location_lat,
                    'lng' => (float) $post->location_lng,
                    'intensity' => $this->severityIntensity($severity),
                    'title' => $post->title,
                    'url' => route('community.show', $post),
                    'category' => data_get($post->meta, 'community_issue_category'),
                    'severity' => filled($severity) ? $severity : null,
                    'status' => data_get($post->meta, 'community_issue_status_tracker'),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<array{
     *     user: User,
     *     badges: list<string>,
     *     issues_reported: int,
     *     supports_given: int,
     *     verifications_given: int,
     *     issues_resolved: int,
     *     score: int,
     * }>
     */
    public function communityChampions(int $limit = 8, ?User $viewer = null): array
    {
        $issuePostIds = $this->publishedIssuesQuery($viewer)->pluck('id');

        if ($issuePostIds->isEmpty()) {
            return [];
        }

        $reporterCounts = CommunityPost::query()
            ->whereIn('id', $issuePostIds)
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $supportCounts = CommunityReportSupport::query()
            ->whereIn('community_post_id', $issuePostIds)
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $verificationCounts = CommunityReportAgreement::query()
            ->whereIn('community_post_id', $issuePostIds)
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $resolvedCounts = CommunityPost::query()
            ->whereIn('id', $issuePostIds)
            ->whereIn('meta->community_issue_status_tracker', CommunityContentTaxonomy::communityIssueResolvedStatuses())
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $waterCounts = CommunityPost::query()
            ->whereIn('id', $issuePostIds)
            ->whereIn('meta->community_issue_category', CommunityContentTaxonomy::communityIssueWaterCategories())
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $greenCounts = CommunityPost::query()
            ->whereIn('id', $issuePostIds)
            ->whereIn('meta->community_issue_category', CommunityContentTaxonomy::communityIssueGreenCategories())
            ->selectRaw('user_id, COUNT(*) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $candidateIds = collect()
            ->merge($reporterCounts->keys())
            ->merge($supportCounts->keys())
            ->merge($verificationCounts->keys())
            ->unique()
            ->values();

        if ($candidateIds->isEmpty()) {
            return [];
        }

        $users = User::query()
            ->whereIn('id', $candidateIds)
            ->get()
            ->keyBy('id');

        return $candidateIds
            ->map(function (int $userId) use (
                $users,
                $reporterCounts,
                $supportCounts,
                $verificationCounts,
                $resolvedCounts,
                $waterCounts,
                $greenCounts,
            ): ?array {
                $user = $users->get($userId);

                if ($user === null) {
                    return null;
                }

                $issuesReported = (int) ($reporterCounts[$userId] ?? 0);
                $supportsGiven = (int) ($supportCounts[$userId] ?? 0);
                $verificationsGiven = (int) ($verificationCounts[$userId] ?? 0);
                $issuesResolved = (int) ($resolvedCounts[$userId] ?? 0);
                $waterIssues = (int) ($waterCounts[$userId] ?? 0);
                $greenIssues = (int) ($greenCounts[$userId] ?? 0);

                $badges = $this->badgesForStats([
                    'issues_reported' => $issuesReported,
                    'supports_given' => $supportsGiven + $verificationsGiven,
                    'issues_resolved' => $issuesResolved,
                    'water_issues' => $waterIssues,
                    'green_issues' => $greenIssues,
                ]);

                if ($badges === []) {
                    return null;
                }

                return [
                    'user' => $user,
                    'badges' => $badges,
                    'issues_reported' => $issuesReported,
                    'supports_given' => $supportsGiven,
                    'verifications_given' => $verificationsGiven,
                    'issues_resolved' => $issuesResolved,
                    'score' => count($badges) * 10 + $issuesReported * 3 + $supportsGiven + $verificationsGiven + ($issuesResolved * 5),
                ];
            })
            ->filter()
            ->sortByDesc('score')
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @param  array{issues_reported: int, supports_given: int, issues_resolved: int, water_issues: int, green_issues: int}  $stats
     * @return list<string>
     */
    public function badgesForStats(array $stats): array
    {
        $definitions = CommunityContentTaxonomy::communityIssueChampionBadgeDefinitions();
        $badges = [];

        if ($stats['issues_reported'] >= $definitions['Issue Reporter']['threshold']) {
            $badges[] = 'Issue Reporter';
        }

        if ($stats['supports_given'] >= $definitions['Community Volunteer']['threshold']) {
            $badges[] = 'Community Volunteer';
        }

        if ($stats['issues_resolved'] >= $definitions['Problem Solver']['threshold']) {
            $badges[] = 'Problem Solver';
        }

        if ($stats['water_issues'] >= $definitions['Water Warrior']['threshold']) {
            $badges[] = 'Water Warrior';
        }

        if ($stats['green_issues'] >= $definitions['Green Champion']['threshold']) {
            $badges[] = 'Green Champion';
        }

        return $badges;
    }

    /**
     * @return Builder<CommunityPost>
     */
    public function publishedIssuesQuery(?User $viewer = null): Builder
    {
        return CommunityPost::query()
            ->where('content_type', 'community-issues')
            ->publiclyListed()
            ->visibleInCommunityListing($viewer);
    }

    private function severityIntensity(string $severity): float
    {
        return match ($severity) {
            'Emergency' => 1.0,
            'Critical' => 0.9,
            'High' => 0.75,
            'Medium' => 0.5,
            'Low' => 0.3,
            default => 0.4,
        };
    }
}
