<?php

namespace App\Http\Controllers\Discussion;

use App\Http\Controllers\Controller;
use App\Models\DiscussionTopic;
use App\Services\DiscussionReadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscussionReadController extends Controller
{
    public function __construct(private DiscussionReadService $readService) {}

    public function summary(Request $request): JsonResponse
    {
        return response()->json($this->readService->unreadSummary($request->user()));
    }

    public function markRead(Request $request, DiscussionTopic $topic): JsonResponse
    {
        $this->authorize('view', $topic);

        $this->readService->markAsRead($request->user(), $topic);

        return response()->json([
            'message' => 'Marked as read.',
            'unread_count' => 0,
            'global_unread' => $this->readService->globalUnreadCount($request->user()),
        ]);
    }
}
