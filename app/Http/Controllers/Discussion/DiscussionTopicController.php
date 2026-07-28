<?php

namespace App\Http\Controllers\Discussion;

use App\Events\Discussion\TopicCreated;
use App\Events\Discussion\TopicPinned;
use App\Http\Controllers\Controller;
use App\Models\DiscussionTopic;
use App\Services\DiscussionReadService;
use App\Support\DiscussionAttachments;
use App\Support\DiscussionFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class DiscussionTopicController extends Controller
{
    public function __construct(private DiscussionReadService $readService) {}

    public function index(Request $request): View|JsonResponse
    {
        $canPin = $request->user()->isAdmin();

        if ($request->expectsJson()) {
            $topics = DiscussionTopic::query()
                ->visibleTo($request->user())
                ->with('user')
                ->withCount(['reactions', 'members'])
                ->orderByDesc('is_pinned')
                ->orderByDesc('pinned_at')
                ->latest()
                ->get();

            $unreadCounts = $this->readService->unreadCountsForTopics($request->user(), $topics);

            return response()->json([
                'topics' => $topics->map(function (DiscussionTopic $topic) use ($unreadCounts) {
                    return array_merge($topic->toBroadcastArray(), [
                        'unread_count' => $unreadCounts[$topic->id] ?? 0,
                    ]);
                })->values(),
                'can_pin' => $canPin,
                'global_unread' => array_sum($unreadCounts),
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $topics->count(),
                    'total' => $topics->count(),
                ],
            ]);
        }

        $topics = DiscussionTopic::query()
            ->visibleTo($request->user())
            ->with('user')
            ->withCount(['reactions', 'members'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('pinned_at')
            ->latest()
            ->paginate(20);

        $unreadCounts = $this->readService->unreadCountsForTopics($request->user(), $topics->getCollection());

        return view('discussions.index', [
            'topics' => $topics,
            'canPin' => $canPin,
            'unreadCounts' => $unreadCounts,
        ]);
    }

    public function messenger(Request $request, ?DiscussionTopic $topic = null): View
    {
        return view('discussions.messenger', [
            'initialTopicId' => $topic?->id,
            'canPin' => $request->user()->isAdmin(),
        ]);
    }

    public function show(Request $request, DiscussionTopic $topic): View|JsonResponse
    {
        $this->authorize('view', $topic);

        $topic->load([
            'user',
            'members',
            'replies.user',
            'replies.reactions',
            'reactions',
        ]);

        $this->readService->markAsRead($request->user(), $topic);

        $canPin = auth()->user()?->isAdmin() ?? false;
        $userReactions = $this->userReactionsForTopic($topic);

        if ($request->expectsJson()) {
            return response()->json([
                'topic' => array_merge($topic->toBroadcastArray(), [
                    'reaction_counts' => $topic->reactionCounts(),
                    'user_reactions' => $userReactions['topic'],
                    'members' => $topic->is_group ? $topic->memberSummaries() : [],
                    'replies' => $topic->replies->map(function ($reply) use ($userReactions) {
                        return array_merge($reply->toBroadcastArray(), [
                            'user_reactions' => $userReactions['replies'][$reply->id] ?? [],
                        ]);
                    })->values(),
                ]),
                'can_pin' => $canPin,
                'global_unread' => $this->readService->globalUnreadCount($request->user()),
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
            'is_group' => ['nullable', 'boolean'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
            'attachments' => ['nullable', 'array', 'max:4'],
            'attachments.*' => ['file', DiscussionAttachments::validationMimesRule(), 'max:10240'],
        ]);

        $attachments = $this->storeAttachments($request->file('attachments', []));
        $isGroup = $request->boolean('is_group');

        $topic = DiscussionTopic::query()->create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'is_group' => $isGroup,
            'body' => $data['body'] ?? null,
            'attachments' => $attachments ?: null,
        ]);

        if ($isGroup) {
            $memberIds = collect($data['member_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $topic->syncGroupMembers($request->user(), $memberIds);
        }

        $topic->load(['user', 'members']);
        $topic->loadCount('members');
        $this->readService->markAsRead($request->user(), $topic);

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
     * @param  list<UploadedFile>  $files
     * @return list<array<string, mixed>>
     */
    private function storeAttachments(array $files): array
    {
        return DiscussionFileUploader::storeMany($files, 'topics');
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
