<?php

namespace App\Http\Controllers\Discussion;

use App\Events\Discussion\TopicCreated;
use App\Events\Discussion\TopicPinned;
use App\Http\Controllers\Controller;
use App\Models\DiscussionGroupInvitation;
use App\Models\DiscussionTopic;
use App\Models\User;
use App\Services\DiscussionGroupInvitationService;
use App\Services\DiscussionReadService;
use App\Services\DiscussionOnlineService;
use App\Services\FoulWordFilter;
use App\Support\DiscussionAttachments;
use App\Support\DiscussionFileUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class DiscussionTopicController extends Controller
{
    public function __construct(
        private DiscussionReadService $readService,
        private DiscussionOnlineService $onlineService,
        private FoulWordFilter $foulWordFilter,
        private DiscussionGroupInvitationService $invitationService,
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $canPin = $request->user()->isAdmin();

        if ($request->expectsJson()) {
            $topics = DiscussionTopic::query()
                ->visibleTo($request->user())
                ->rootOnly()
                ->with('user')
                ->withCount(['reactions', 'members', 'children'])
                ->orderByDesc('is_pinned')
                ->orderByDesc('pinned_at')
                ->latest()
                ->get();

            $unreadCounts = $this->readService->unreadCountsForRootTopics($request->user(), $topics);
            $pendingInvitations = $this->pendingInvitationsForUser($request->user());

            return response()->json([
                'topics' => $topics->map(function (DiscussionTopic $topic) use ($unreadCounts) {
                    return array_merge($topic->toBroadcastArray(), [
                        'unread_count' => $unreadCounts[$topic->id] ?? 0,
                    ]);
                })->values(),
                'pending_invitations' => $pendingInvitations,
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
            ->rootOnly()
            ->with('user')
            ->withCount(['reactions', 'members', 'children'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('pinned_at')
            ->latest()
            ->paginate(20);

        $unreadCounts = $this->readService->unreadCountsForRootTopics($request->user(), $topics->getCollection());

        return view('discussions.index', [
            'topics' => $topics,
            'canPin' => $canPin,
            'unreadCounts' => $unreadCounts,
            'pendingInvitations' => $this->pendingInvitationModelsForUser($request->user()),
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

        if ($topic->isGroupContainer()) {
            $topic->load(['user', 'members']);
            $topic->loadCount(['members', 'children']);

            $children = $topic->children()
                ->with('user')
                ->withCount('replies')
                ->latest()
                ->get();

            $unreadCounts = $this->readService->unreadCountsForTopics($request->user(), $children);
            $canPin = auth()->user()?->isAdmin() ?? false;

            if ($request->expectsJson()) {
                return response()->json([
                    'topic' => array_merge($topic->toBroadcastArray(), [
                        'children' => $children->map(function (DiscussionTopic $child) use ($unreadCounts) {
                            return array_merge($child->toBroadcastArray(), [
                                'unread_count' => $unreadCounts[$child->id] ?? 0,
                            ]);
                        })->values(),
                        'online_users' => $this->onlineService->onlineUsersForTopic($topic),
                        'members' => $this->onlineService->membersWithOnlineStatus($topic),
                    ]),
                    'can_pin' => $canPin,
                    'global_unread' => $this->readService->globalUnreadCount($request->user()),
                ]);
            }

            return view('discussions.show', [
                'topic' => $topic,
                'canPin' => $canPin,
                'userReactions' => ['topic' => [], 'replies' => []],
            ]);
        }

        $topic->load([
            'user',
            'parent',
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
                    'members' => $topic->isGroupContainer()
                        ? $this->onlineService->membersWithOnlineStatus($topic)
                        : ($topic->parent_topic_id && $topic->parent?->isGroupContainer()
                            ? $this->onlineService->membersWithOnlineStatus($topic->parent)
                            : []),
                    'online_users' => $this->onlineService->onlineUsersForTopic($topic),
                    'parent' => $topic->parent
                        ? array_merge($topic->parent->toBroadcastArray(), [
                            'children_count' => $topic->parent->children()->count(),
                        ])
                        : null,
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
        $isGroupContainer = $request->boolean('is_group') && ! $request->filled('parent_topic_id');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'body' => ['nullable', 'string', 'max:'.($isGroupContainer ? '1000' : '5000')],
            'is_group' => ['nullable', 'boolean'],
            'parent_topic_id' => ['nullable', 'integer', 'exists:discussion_topics,id'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
            'phone_numbers' => ['nullable', 'array'],
            'phone_numbers.*' => ['string', 'regex:/^[0-9]{10,15}$/'],
            'group_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'attachments' => ['nullable', 'array', 'max:4'],
            'attachments.*' => ['file', DiscussionAttachments::validationMimesRule(), 'max:10240'],
        ]);

        $this->foulWordFilter->assertCleanFields([
            'title' => $data['title'] ?? '',
            'body' => $data['body'] ?? '',
        ]);

        $parentTopic = null;

        if (! empty($data['parent_topic_id'])) {
            $parentTopic = DiscussionTopic::query()->findOrFail((int) $data['parent_topic_id']);
            $this->authorize('createInGroup', $parentTopic);
        }

        $isGroup = $parentTopic ? false : $request->boolean('is_group');
        $attachments = $isGroup
            ? []
            : $this->storeAttachments($request->file('attachments', []));
        $groupImagePath = $isGroup && $request->hasFile('group_image')
            ? $this->storeGroupImage($request->file('group_image'))
            : null;

        $topic = DiscussionTopic::query()->create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'is_group' => $isGroup,
            'parent_topic_id' => $parentTopic?->id,
            'group_image' => $groupImagePath,
            'body' => $data['body'] ?? null,
            'attachments' => $attachments ?: null,
        ]);

        if ($isGroup) {
            $memberIds = collect($data['member_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            $phoneNumbers = collect($data['phone_numbers'] ?? [])
                ->map(fn ($phone) => DiscussionGroupInvitation::normalizePhone($phone))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $topic->syncGroupMembers($request->user(), []);
            $invited = array_merge(
                $this->invitationService->inviteMembers($topic, $request->user(), $memberIds),
                $this->invitationService->inviteByPhoneNumbers($topic, $request->user(), $phoneNumbers),
            );
        }

        $topic->load(['user', 'members', 'parent']);
        $topic->loadCount(['members', 'children']);
        $this->readService->markAsRead($request->user(), $topic);

        TopicCreated::dispatch($topic);

        if ($request->expectsJson()) {
            $message = $parentTopic
                ? 'Group topic created.'
                : ($isGroup
                    ? (count($invited ?? []) > 0
                        ? 'Group created. Invitations sent — members join after they approve.'
                        : 'Group created.')
                    : 'Topic created.');

            return response()->json([
                'message' => $message,
                'topic' => $topic->toBroadcastArray(),
                'pending_invitations' => isset($invited)
                    ? collect($invited)->map->toBroadcastArray()->values()
                    : [],
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

    public function updateGroupImage(Request $request, DiscussionTopic $topic): JsonResponse
    {
        $this->authorize('manageMembers', $topic);

        abort_unless($topic->isGroupContainer(), 404);

        $request->validate([
            'group_image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        $topic->deleteStoredGroupImage();
        $topic->update([
            'group_image' => $this->storeGroupImage($request->file('group_image')),
        ]);

        return response()->json([
            'message' => 'Group photo updated.',
            'group_image_url' => $topic->fresh()->groupImageUrl(),
            'topic' => $topic->fresh(['user', 'members'])->loadCount('members')->toBroadcastArray(),
        ]);
    }

    public function destroyGroupImage(Request $request, DiscussionTopic $topic): JsonResponse
    {
        $this->authorize('manageMembers', $topic);

        abort_unless($topic->is_group, 404);

        $topic->deleteStoredGroupImage();
        $topic->update(['group_image' => null]);

        return response()->json([
            'message' => 'Group photo removed.',
            'group_image_url' => null,
            'topic' => $topic->fresh(['user', 'members'])->loadCount('members')->toBroadcastArray(),
        ]);
    }

    public function updateGroupSettings(Request $request, DiscussionTopic $topic): JsonResponse
    {
        $this->authorize('manageMembers', $topic);

        abort_unless($topic->isGroupContainer(), 404);

        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'body' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->foulWordFilter->assertCleanFields(array_filter([
            'title' => $data['title'] ?? null,
            'body' => array_key_exists('body', $data) ? $data['body'] : null,
        ], fn ($value) => $value !== null));

        $topic->update($data);

        $topic = $topic->fresh(['user', 'members'])->loadCount(['members', 'children']);

        return response()->json([
            'message' => 'Group settings updated.',
            'topic' => $topic->toBroadcastArray(),
        ]);
    }

    public function destroyGroup(Request $request, DiscussionTopic $topic): JsonResponse
    {
        $this->authorize('deleteGroup', $topic);

        abort_unless($topic->isGroupContainer(), 404);

        $groupId = $topic->id;
        $topic->deleteGroupCompletely();

        return response()->json([
            'message' => 'Group deleted. All topics, chats, and files in this group were removed.',
            'deleted_group_id' => $groupId,
        ]);
    }

    /**
     * @param  list<UploadedFile>  $files
     * @return list<array<string, mixed>>
     */
    private function storeAttachments(array $files): array
    {
        return DiscussionFileUploader::storeMany($files, 'topics');
    }

    private function storeGroupImage(UploadedFile $file): string
    {
        return DiscussionFileUploader::storeMedia($file, 'group-images')['path'];
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

    /**
     * @return list<array<string, mixed>>
     */
    private function pendingInvitationsForUser(User $user): array
    {
        return $this->pendingInvitationModelsForUser($user)
            ->map(fn (DiscussionGroupInvitation $invitation) => $invitation->toBroadcastArray())
            ->values()
            ->all();
    }

    /**
     * @return \Illuminate\Support\Collection<int, DiscussionGroupInvitation>
     */
    private function pendingInvitationModelsForUser(User $user)
    {
        return DiscussionGroupInvitation::query()
            ->with(['topic', 'inviter', 'invitee'])
            ->pending()
            ->where('invitee_id', $user->id)
            ->latest()
            ->get();
    }
}
