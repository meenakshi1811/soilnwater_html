<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityLocalVoiceFollow;
use App\Models\CommunityLocalVoiceSupport;
use App\Models\CommunityPost;
use App\Services\CommunityLocalVoiceEngagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommunityLocalVoiceEngagementController extends Controller
{
    public function toggleSupport(Request $request, CommunityPost $post): JsonResponse
    {
        $this->authorizeLocalVoiceEngagement($post, $request);
        abort_unless($post->allowsLocalVoiceSupport(), 404);

        $existing = CommunityLocalVoiceSupport::query()
            ->where('community_post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $active = false;
            $message = 'Support removed from this local voice post.';
        } else {
            CommunityLocalVoiceSupport::query()->create([
                'community_post_id' => $post->id,
                'user_id' => $request->user()->id,
            ]);
            $active = true;
            $message = 'Thank you for supporting this local voice.';
        }

        return $this->engagementResponse($post, $request, $message, [
            'supported' => $active,
        ]);
    }

    public function toggleFollow(Request $request, CommunityPost $post): JsonResponse
    {
        $this->authorizeLocalVoiceEngagement($post, $request);
        abort_unless($post->allowsLocalVoiceFollow(), 404);

        $existing = CommunityLocalVoiceFollow::query()
            ->where('community_post_id', $post->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $active = false;
            $message = 'You will no longer receive updates for this issue.';
        } else {
            CommunityLocalVoiceFollow::query()->create([
                'community_post_id' => $post->id,
                'user_id' => $request->user()->id,
            ]);
            $active = true;
            $message = 'You are now following this issue for updates.';
        }

        return $this->engagementResponse($post, $request, $message, [
            'following' => $active,
        ]);
    }

    private function authorizeLocalVoiceEngagement(CommunityPost $post, Request $request): void
    {
        abort_unless($post->isPubliclyVisible(), 404);
        abort_unless($post->isLocalVoicesPost(), 404);
        abort_unless($post->isVisibleInCommunityTo($request->user()), 403);
        abort_if($post->user_id === $request->user()->id, 422, 'You cannot use community actions on your own post.');
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function engagementResponse(CommunityPost $post, Request $request, string $message, array $extra = []): JsonResponse
    {
        return response()->json(array_merge([
            'message' => $message,
            'engagement' => CommunityLocalVoiceEngagementService::stateForPost($post->fresh(), $request->user()->id),
        ], $extra));
    }
}
