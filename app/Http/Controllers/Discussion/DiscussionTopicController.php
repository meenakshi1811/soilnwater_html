<?php

namespace App\Http\Controllers\Discussion;

use App\Events\Discussion\TopicCreated;
use App\Events\Discussion\TopicPinned;
use App\Http\Controllers\Controller;
use App\Models\DiscussionTopic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscussionTopicController extends Controller
{
    public function index(Request $request): View
    {
        $topics = DiscussionTopic::query()
            ->with('user')
            ->withCount('reactions')
            ->orderByDesc('is_pinned')
            ->orderByDesc('pinned_at')
            ->latest()
            ->paginate(20);

        return view('discussions.index', [
            'topics' => $topics,
            'canPin' => $request->user()->isAdmin(),
        ]);
    }

    public function show(DiscussionTopic $topic): View
    {
        $topic->load([
            'user',
            'replies.user',
            'replies.reactions',
            'reactions',
        ]);

        return view('discussions.show', [
            'topic' => $topic,
            'canPin' => auth()->user()?->isAdmin() ?? false,
            'userReactions' => $this->userReactionsForTopic($topic),
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        $topic = DiscussionTopic::query()->create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
        ]);

        $topic->load('user');

        TopicCreated::dispatch($topic);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Topic created.',
                'topic' => $topic->toBroadcastArray(),
            ]);
        }

        return redirect()
            ->route('discussions.show', $topic)
            ->with('success', 'Topic created.');
    }

    public function pin(Request $request, DiscussionTopic $topic): JsonResponse|RedirectResponse
    {
        $this->authorize('pin', $topic);

        $isPinned = ! $topic->is_pinned;

        $topic->update([
            'is_pinned' => $isPinned,
            'pinned_by' => $isPinned ? $request->user()->id : null,
            'pinned_at' => $isPinned ? now() : null,
        ]);

        TopicPinned::dispatch($topic->fresh());

        $message = $isPinned ? 'Topic pinned.' : 'Topic unpinned.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'is_pinned' => $topic->is_pinned,
            ]);
        }

        return back()->with('success', $message);
    }

    /**
     * @return array<string, list<string>>
     */
    private function userReactionsForTopic(DiscussionTopic $topic): array
    {
        $userId = auth()->id();

        if (! $userId) {
            return ['topic' => [], 'replies' => []];
        }

        $topicReactions = $topic->reactions()
            ->where('user_id', $userId)
            ->pluck('reaction')
            ->all();

        $replyReactions = [];
        foreach ($topic->replies as $reply) {
            $replyReactions[$reply->id] = $reply->reactions
                ->where('user_id', $userId)
                ->pluck('reaction')
                ->all();
        }

        return [
            'topic' => $topicReactions,
            'replies' => $replyReactions,
        ];
    }
}
