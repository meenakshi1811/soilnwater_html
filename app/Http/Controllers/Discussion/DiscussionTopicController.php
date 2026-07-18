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
    public function index(Request $request): View|JsonResponse
    {
        $topics = DiscussionTopic::query()
            ->with('user')
            ->withCount('reactions')
            ->orderByDesc('is_pinned')
            ->orderByDesc('pinned_at')
            ->latest()
            ->paginate(20);

        $canPin = $request->user()->isAdmin();

        if ($request->expectsJson()) {
            return response()->json([
                'topics' => $topics->getCollection()->map->toBroadcastArray()->values(),
                'can_pin' => $canPin,
                'meta' => [
                    'current_page' => $topics->currentPage(),
                    'last_page' => $topics->lastPage(),
                    'per_page' => $topics->perPage(),
                    'total' => $topics->total(),
                ],
            ]);
        }

        return view('discussions.index', [
            'topics' => $topics,
            'canPin' => $canPin,
        ]);
    }

    public function show(Request $request, DiscussionTopic $topic): View|JsonResponse
    {
        $topic->load([
            'user',
            'replies.user',
            'replies.reactions',
            'reactions',
        ]);

        $canPin = auth()->user()?->isAdmin() ?? false;
        $userReactions = $this->userReactionsForTopic($topic);

        if ($request->expectsJson()) {
            return response()->json([
                'topic' => array_merge($topic->toBroadcastArray(), [
                    'reaction_counts' => $topic->reactionCounts(),
                    'user_reactions' => $userReactions['topic'],
                    'replies' => $topic->replies->map(function ($reply) use ($userReactions) {
                        return array_merge($reply->toBroadcastArray(), [
                            'user_reactions' => $userReactions['replies'][$reply->id] ?? [],
                        ]);
                    })->values(),
                ]),
                'can_pin' => $canPin,
            ]);
        }

        return view('discussions.show', [
            'topic' => $topic,
            'canPin' => $canPin,
            'userReactions' => $userReactions,
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
