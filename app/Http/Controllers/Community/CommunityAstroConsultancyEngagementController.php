<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityAstroConsultancyPrivateQuery;
use App\Models\CommunityPost;
use App\Services\CommunityAstroConsultancyEngagementNotificationService;
use App\Services\CommunityAstroConsultancyEngagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommunityAstroConsultancyEngagementController extends Controller
{
    public function submitPrivateQuery(Request $request, CommunityPost $post): JsonResponse
    {
        abort_unless($post->isPubliclyVisible(), 404);
        abort_unless($post->isAstroConsultancyPost(), 404);
        abort_unless($post->astroHasPrivateQueryActions(), 404);

        $allowedTypes = $post->astroPrivateQueryOptionsForDisplay();
        abort_if($allowedTypes === [], 422, 'Private query options are not enabled for this post.');

        $data = $request->validate([
            'query_type' => ['required', 'string', 'max:80', Rule::in($allowedTypes)],
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160'],
            'mobile' => ['nullable', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $user = $request->user();
        $userId = $user?->id;

        abort_if($userId !== null && $post->user_id === $userId, 422, 'You cannot send a consultation request on your own post.');

        if ($userId !== null) {
            $existing = CommunityAstroConsultancyPrivateQuery::query()
                ->where('community_post_id', $post->id)
                ->where('user_id', $userId)
                ->where('query_type', $data['query_type'])
                ->where('created_at', '>=', now()->subHours(24))
                ->exists();

            if ($existing) {
                return response()->json([
                    'message' => 'You already sent this type of request recently. Please wait before submitting again.',
                    'engagement' => CommunityAstroConsultancyEngagementService::stateForPost($post->fresh(), $userId),
                ], 422);
            }
        }

        CommunityAstroConsultancyPrivateQuery::query()->create([
            'community_post_id' => $post->id,
            'user_id' => $userId,
            'query_type' => $data['query_type'],
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'message' => $data['message'],
        ]);

        if ($user !== null) {
            CommunityAstroConsultancyEngagementNotificationService::notifyAuthorOfPrivateQuery(
                $post,
                $user,
                $data['query_type'],
                $data['message']
            );
            CommunityAstroConsultancyEngagementNotificationService::notifySubmitterOfPrivateQueryConfirmation(
                $post,
                $user,
                $data['query_type']
            );
        } else {
            CommunityAstroConsultancyEngagementNotificationService::notifyAuthorOfGuestPrivateQuery(
                $post,
                $data['name'],
                $data['query_type'],
                $data['message']
            );
        }

        return response()->json([
            'message' => 'Thank you. Your private consultation request has been sent to the author.',
            'engagement' => CommunityAstroConsultancyEngagementService::stateForPost($post->fresh(), $userId),
        ]);
    }
}
