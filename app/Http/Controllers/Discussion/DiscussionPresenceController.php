<?php

namespace App\Http\Controllers\Discussion;

use App\Http\Controllers\Controller;
use App\Models\DiscussionTopic;
use App\Services\DiscussionOnlineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscussionPresenceController extends Controller
{
    public function __construct(private DiscussionOnlineService $onlineService) {}

    public function show(Request $request, DiscussionTopic $topic): JsonResponse
    {
        $this->authorize('view', $topic);

        $context = $topic->isGroupContainer() || $topic->parent_topic_id
            ? 'members'
            : 'participants';

        return response()->json([
            'context' => $context,
            'online_users' => $this->onlineService->onlineUsersForTopic($topic),
            'members' => $this->membersForTopic($topic),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function membersForTopic(DiscussionTopic $topic): array
    {
        if ($topic->isGroupContainer()) {
            return $this->onlineService->membersWithOnlineStatus($topic);
        }

        if ($topic->parent_topic_id) {
            $topic->loadMissing('parent');

            if ($topic->parent?->isGroupContainer()) {
                return $this->onlineService->membersWithOnlineStatus($topic->parent);
            }
        }

        return [];
    }
}
