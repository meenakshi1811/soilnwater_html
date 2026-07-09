<?php

namespace App\Http\Controllers\Discussion;

use App\Events\Discussion\ReplyCreated;
use App\Http\Controllers\Controller;
use App\Models\DiscussionReply;
use App\Models\DiscussionTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DiscussionReplyController extends Controller
{
    public function store(Request $request, DiscussionTopic $topic): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
            'parent_id' => ['nullable', 'integer', 'exists:discussion_replies,id'],
        ]);

        if (! empty($data['parent_id'])) {
            $parent = DiscussionReply::query()->findOrFail($data['parent_id']);
            abort_unless($parent->discussion_topic_id === $topic->id, 422);
        }

        $reply = DiscussionReply::query()->create([
            'discussion_topic_id' => $topic->id,
            'user_id' => $request->user()->id,
            'parent_id' => $data['parent_id'] ?? null,
            'body' => $data['body'],
        ]);

        $topic->increment('replies_count');

        $reply->load('user');

        ReplyCreated::dispatch($reply);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Reply posted.',
                'reply' => $reply->toBroadcastArray(),
            ]);
        }

        return back()->with('success', 'Reply posted.');
    }
}
