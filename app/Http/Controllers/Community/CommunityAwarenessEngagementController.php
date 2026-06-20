<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityAwarenessPledge;
use App\Models\CommunityAwarenessSupport;
use App\Models\CommunityAwarenessVolunteer;
use App\Models\CommunityPost;
use App\Services\CommunityAwarenessEngagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommunityAwarenessEngagementController extends Controller
{
    public function toggleSupport(Request $request, CommunityPost $post): JsonResponse
    {
        $this->authorizeAwarenessEngagement($post, $request);
        abort_unless($post->allowsAwarenessCauseSupport(), 404);

        $existing = CommunityAwarenessSupport::query()
            ->where('community_post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $active = false;
            $message = 'Your support was removed.';
        } else {
            CommunityAwarenessSupport::query()->create([
                'community_post_id' => $post->id,
                'user_id' => $request->user()->id,
            ]);
            $active = true;
            $message = 'Thank you for supporting this cause.';
            \App\Services\CommunityAwarenessEngagementNotificationService::notifyAuthorOfSupport($post, $request->user());
        }

        return $this->engagementResponse($post, $request, $message, [
            'supported' => $active,
        ]);
    }

    public function pledge(Request $request, CommunityPost $post): JsonResponse
    {
        $this->authorizeAwarenessEngagement($post, $request);
        abort_unless($post->allowsAwarenessPledges(), 404);

        $pledgeOptions = $post->awarenessPledgeOptions();
        abort_if($pledgeOptions === [], 422, 'Pledges are not configured for this campaign.');

        $data = $request->validate([
            'pledge_text' => ['required', 'string', 'max:255', Rule::in($pledgeOptions)],
        ]);

        $existingPledge = CommunityAwarenessPledge::query()
            ->where('community_post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        CommunityAwarenessPledge::query()->updateOrCreate(
            [
                'community_post_id' => $post->id,
                'user_id' => $request->user()->id,
            ],
            [
                'pledge_text' => $data['pledge_text'],
            ]
        );

        if (! $existingPledge || $existingPledge->pledge_text !== $data['pledge_text']) {
            \App\Services\CommunityAwarenessEngagementNotificationService::notifyAuthorOfPledge(
                $post,
                $request->user(),
                $data['pledge_text']
            );
        }

        return $this->engagementResponse($post, $request, 'Your pledge has been recorded.', [
            'pledge_text' => $data['pledge_text'],
        ]);
    }

    public function volunteer(Request $request, CommunityPost $post): JsonResponse
    {
        abort_unless($post->isPubliclyVisible(), 404);
        abort_unless($post->isAwarenessPost(), 404);
        abort_unless($post->allowsCampaignJoin(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'mobile' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'city' => ['nullable', 'string', 'max:120'],
        ]);

        $user = $request->user();
        $userId = $user?->id;

        abort_if($userId !== null && $post->user_id === $userId, 422, 'You cannot join your own campaign.');

        if ($userId !== null) {
            $existing = CommunityAwarenessVolunteer::query()
                ->where('community_post_id', $post->id)
                ->where('user_id', $userId)
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'You have already joined this campaign.',
                    'engagement' => CommunityAwarenessEngagementService::stateForPost($post->fresh(), $userId),
                ], 422);
            }
        } else {
            $duplicateGuest = CommunityAwarenessVolunteer::query()
                ->where('community_post_id', $post->id)
                ->whereNull('user_id')
                ->where('mobile', $data['mobile'])
                ->exists();

            if ($duplicateGuest) {
                return response()->json([
                    'message' => 'This mobile number has already been registered for this campaign.',
                    'engagement' => CommunityAwarenessEngagementService::stateForPost($post->fresh(), null),
                ], 422);
            }
        }

        CommunityAwarenessVolunteer::query()->create([
            'community_post_id' => $post->id,
            'user_id' => $userId,
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'] ?? null,
            'city' => $data['city'] ?? null,
        ]);

        \App\Services\CommunityAwarenessEngagementNotificationService::notifyAuthorOfVolunteer(
            $post,
            $data['name'],
            $user
        );

        return response()->json([
            'message' => 'Thank you for joining this campaign.',
            'engagement' => CommunityAwarenessEngagementService::stateForPost($post->fresh(), $userId),
        ]);
    }

    private function authorizeAwarenessEngagement(CommunityPost $post, Request $request): void
    {
        abort_unless($post->isPubliclyVisible(), 404);
        abort_unless($post->isAwarenessPost(), 404);
        abort_if($post->user_id === $request->user()->id, 422, 'You cannot use campaign actions on your own post.');
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function engagementResponse(CommunityPost $post, Request $request, string $message, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'message' => $message,
            'engagement' => CommunityAwarenessEngagementService::stateForPost($post->fresh(), $request->user()->id),
            'pledge_counts' => CommunityAwarenessEngagementService::pledgeCounts($post->fresh()),
        ], $extra));
    }
}
