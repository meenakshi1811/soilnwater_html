<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\CommunityReportAgreement;
use App\Models\CommunityReportFollow;
use App\Models\CommunityReportSupport;
use App\Services\CommunityCommunityIssuesEngagementNotificationService;
use App\Services\CommunityReportEngagementNotificationService;
use App\Services\CommunityReportTrustScoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityReportEngagementController extends Controller
{
    public function toggleSupport(Request $request, CommunityPost $post): JsonResponse
    {
        $this->authorizeReportEngagement($post, $request, 'support');

        $previousSupportCount = $post->reportSupports()->count();

        $existing = CommunityReportSupport::query()
            ->where('community_post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $active = false;
            $message = 'Support removed from this report.';
        } else {
            CommunityReportSupport::query()->create([
                'community_post_id' => $post->id,
                'user_id' => $request->user()->id,
            ]);
            $active = true;
            $message = 'Thank you for supporting this report.';
            CommunityReportEngagementNotificationService::notifyAuthorOfSupport($post, $request->user());
            CommunityCommunityIssuesEngagementNotificationService::maybeNotifyEscalation($post->fresh(), $previousSupportCount);
        }

        return $this->engagementResponse($post, $request, $message, [
            'supported' => $active,
        ]);
    }

    public function toggleAgree(Request $request, CommunityPost $post): JsonResponse
    {
        $this->authorizeReportEngagement($post, $request, 'agree');

        $existing = CommunityReportAgreement::query()
            ->where('community_post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $active = false;
            $message = 'Your agreement was removed.';
        } else {
            CommunityReportAgreement::query()->create([
                'community_post_id' => $post->id,
                'user_id' => $request->user()->id,
            ]);
            $active = true;
            $message = 'Your agreement has been recorded.';
            CommunityReportEngagementNotificationService::notifyAuthorOfAgreement($post, $request->user());
        }

        return $this->engagementResponse($post, $request, $message, [
            'agreed' => $active,
        ]);
    }

    public function toggleFollow(Request $request, CommunityPost $post): JsonResponse
    {
        $this->authorizeReportEngagement($post, $request, 'follow');

        $existing = CommunityReportFollow::query()
            ->where('community_post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $active = false;
            $message = 'You will no longer receive updates for this report.';
        } else {
            CommunityReportFollow::query()->create([
                'community_post_id' => $post->id,
                'user_id' => $request->user()->id,
            ]);
            $active = true;
            $message = 'You are now following this report for updates.';
            CommunityReportEngagementNotificationService::notifyAuthorOfFollow($post, $request->user());
        }

        return $this->engagementResponse($post, $request, $message, [
            'following' => $active,
        ]);
    }

    private function authorizeReportEngagement(CommunityPost $post, Request $request, string $action): void
    {
        abort_unless($post->isPubliclyVisible(), 404);
        abort_unless($post->isReportContent() || $post->isMyAreaPost() || $post->isCommunityIssuesPost(), 404);
        abort_if($post->user_id === $request->user()->id, 422, 'You cannot use community reporting actions on your own post.');

        if ($post->isCommunityIssuesPost()) {
            match ($action) {
                'support' => abort_unless($post->allowsCommunityIssueSupport(), 404),
                'agree' => abort_unless($post->allowsCommunityIssueVerification(), 404),
                'follow' => abort_unless($post->allowsCommunityIssueFollow(), 404),
                default => null,
            };
        }
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function engagementResponse(CommunityPost $post, Request $request, string $message, array $extra = []): JsonResponse
    {
        $post = CommunityReportTrustScoreService::syncToMeta($post->fresh());

        return response()->json(array_merge([
            'message' => $message,
            'engagement' => CommunityReportEngagementNotificationService::stateForPost($post, $request->user()->id),
            'report_trust_score' => $post->reportTrustScore(),
        ], $extra));
    }
}
