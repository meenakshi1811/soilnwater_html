<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityEnvironmentFollow;
use App\Models\CommunityEnvironmentSupport;
use App\Models\CommunityEnvironmentVolunteer;
use App\Models\CommunityPost;
use App\Services\CommunityEnvironmentEngagementNotificationService;
use App\Services\CommunityEnvironmentEngagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommunityEnvironmentEngagementController extends Controller
{
    public function toggleSupport(Request $request, CommunityPost $post): JsonResponse
    {
        $this->authorizeEnvironmentEngagement($post, $request);
        abort_unless($post->allowsEnvironmentSupportInitiative(), 404);

        $existing = CommunityEnvironmentSupport::query()
            ->where('community_post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $active = false;
            $message = 'Your support was removed.';
        } else {
            CommunityEnvironmentSupport::query()->create([
                'community_post_id' => $post->id,
                'user_id' => $request->user()->id,
            ]);
            $active = true;
            $message = 'Thank you for supporting this initiative.';
            CommunityEnvironmentEngagementNotificationService::notifyAuthorOfSupport($post, $request->user());
        }

        return $this->engagementResponse($post, $request, $message, [
            'supported' => $active,
        ]);
    }

    public function toggleFollow(Request $request, CommunityPost $post): JsonResponse
    {
        $this->authorizeEnvironmentEngagement($post, $request);
        abort_unless($post->allowsEnvironmentFollowCampaign(), 404);

        $existing = CommunityEnvironmentFollow::query()
            ->where('community_post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $active = false;
            $message = 'You will no longer receive updates for this campaign.';
        } else {
            CommunityEnvironmentFollow::query()->create([
                'community_post_id' => $post->id,
                'user_id' => $request->user()->id,
            ]);
            $active = true;
            $message = 'You are now following this environment campaign.';
            CommunityEnvironmentEngagementNotificationService::notifyAuthorOfFollow($post, $request->user());
        }

        return $this->engagementResponse($post, $request, $message, [
            'following' => $active,
        ]);
    }

    public function volunteer(Request $request, CommunityPost $post): JsonResponse
    {
        abort_unless($post->isPubliclyVisible(), 404);
        abort_unless($post->isEnvironmentPost(), 404);
        abort_unless($post->allowsEnvironmentVolunteerRegistration() || $post->allowsEnvironmentJoinCampaign(), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'mobile' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'city' => ['nullable', 'string', 'max:120'],
            'interest' => ['nullable', 'string', 'max:80', Rule::in($post->environmentParticipationRequests())],
        ]);

        $user = $request->user();
        $userId = $user?->id;

        abort_if($userId !== null && $post->user_id === $userId, 422, 'You cannot volunteer on your own post.');

        if ($userId !== null) {
            $existing = CommunityEnvironmentVolunteer::query()
                ->where('community_post_id', $post->id)
                ->where('user_id', $userId)
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'You have already registered for this campaign.',
                    'engagement' => CommunityEnvironmentEngagementService::stateForPost($post->fresh(), $userId),
                ], 422);
            }
        } else {
            $duplicateGuest = CommunityEnvironmentVolunteer::query()
                ->where('community_post_id', $post->id)
                ->whereNull('user_id')
                ->where('mobile', $data['mobile'])
                ->exists();

            if ($duplicateGuest) {
                return response()->json([
                    'message' => 'This mobile number has already been registered for this campaign.',
                    'engagement' => CommunityEnvironmentEngagementService::stateForPost($post->fresh(), null),
                ], 422);
            }
        }

        CommunityEnvironmentVolunteer::query()->create([
            'community_post_id' => $post->id,
            'user_id' => $userId,
            'name' => $data['name'],
            'mobile' => $data['mobile'],
            'email' => $data['email'] ?? null,
            'city' => $data['city'] ?? null,
            'interest' => $data['interest'] ?? null,
        ]);

        CommunityEnvironmentEngagementNotificationService::notifyAuthorOfVolunteer(
            $post,
            $data['name'],
            $user,
            $data['interest'] ?? null
        );

        return response()->json([
            'message' => 'Thank you for joining this environment campaign.',
            'engagement' => CommunityEnvironmentEngagementService::stateForPost($post->fresh(), $userId),
        ]);
    }

    private function authorizeEnvironmentEngagement(CommunityPost $post, Request $request): void
    {
        abort_unless($post->isPubliclyVisible(), 404);
        abort_unless($post->isEnvironmentPost(), 404);
        abort_if($post->user_id === $request->user()->id, 422, 'You cannot use campaign actions on your own post.');
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function engagementResponse(CommunityPost $post, Request $request, string $message, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'message' => $message,
            'engagement' => CommunityEnvironmentEngagementService::stateForPost($post->fresh(), $request->user()->id),
        ], $extra));
    }
}
