<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Models\CommunityBusinessQuery;
use App\Models\CommunityPost;
use App\Services\CommunityBusinessEngagementNotificationService;
use App\Services\CommunityBusinessEngagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommunityBusinessEngagementController extends Controller
{
    public function submitQuery(Request $request, CommunityPost $post): JsonResponse
    {
        abort_unless($post->isPubliclyVisible(), 404);
        abort_unless($post->isBusinessPost(), 404);
        abort_unless($post->allowsBusinessContact(), 404);

        $allowedTypes = $post->businessContactOptionsForDisplay();
        abort_if($allowedTypes === [], 422, 'Contact options are not enabled for this post.');

        $data = $request->validate([
            'query_type' => ['required', 'string', 'max:80', Rule::in($allowedTypes)],
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:160'],
            'mobile' => ['nullable', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $user = $request->user();
        $userId = $user?->id;

        abort_if($userId !== null && $post->user_id === $userId, 422, 'You cannot send an inquiry on your own post.');

        if ($userId !== null) {
            $existing = CommunityBusinessQuery::query()
                ->where('community_post_id', $post->id)
                ->where('user_id', $userId)
                ->where('query_type', $data['query_type'])
                ->where('created_at', '>=', now()->subHours(24))
                ->exists();

            if ($existing) {
                return response()->json([
                    'message' => 'You already sent this type of inquiry recently. Please wait before submitting again.',
                    'engagement' => CommunityBusinessEngagementService::stateForPost($post->fresh(), $userId),
                ], 422);
            }
        }

        CommunityBusinessQuery::query()->create([
            'community_post_id' => $post->id,
            'user_id' => $userId,
            'query_type' => $data['query_type'],
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'] ?? null,
            'message' => $data['message'],
        ]);

        if ($user !== null) {
            CommunityBusinessEngagementNotificationService::notifyAuthorOfQuery(
                $post,
                $user,
                $data['query_type'],
                $data['message']
            );
            CommunityBusinessEngagementNotificationService::notifySubmitterOfQueryConfirmation(
                $post,
                $user,
                $data['query_type']
            );
        } else {
            CommunityBusinessEngagementNotificationService::notifyAuthorOfGuestQuery(
                $post,
                $data['name'],
                $data['query_type'],
                $data['message']
            );
        }

        return response()->json([
            'message' => 'Thank you. Your message has been sent to the author.',
            'engagement' => CommunityBusinessEngagementService::stateForPost($post->fresh(), $userId),
        ]);
    }
}
