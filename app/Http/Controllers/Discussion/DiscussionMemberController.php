<?php

namespace App\Http\Controllers\Discussion;

use App\Http\Controllers\Controller;
use App\Models\DiscussionTopic;
use App\Models\User;
use App\Services\DiscussionOnlineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiscussionMemberController extends Controller
{
    public function __construct(private DiscussionOnlineService $onlineService) {}

    public function searchUsers(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $query = trim((string) ($data['q'] ?? ''));

        $users = User::query()
            ->where('id', '!=', $request->user()->id)
            ->where('is_blocked', false)
            ->where('is_chat_blocked', false)
            ->when($query !== '', function ($builder) use ($query): void {
                $builder->where(function ($inner) use ($query): void {
                    $inner->where('name', 'like', "%{$query}%")
                        ->orWhere('full_name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                });
            })
            ->orderBy('name')
            ->limit($query === '' ? 50 : 15)
            ->get(['id', 'name', 'full_name', 'email']);

        return response()->json([
            'users' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->authorDisplayName(),
                'email' => $user->email,
                'initials' => $user->authorInitials(),
            ])->values(),
        ]);
    }

    public function index(Request $request, DiscussionTopic $topic): JsonResponse
    {
        $this->authorize('view', $topic);

        abort_unless($topic->isGroupContainer(), 404);

        return response()->json([
            'members' => $this->onlineService->membersWithOnlineStatus($topic),
            'online_users' => $this->onlineService->onlineUsersForTopic($topic),
            'can_manage_members' => $request->user()->can('manageMembers', $topic),
            'can_delete_group' => $request->user()->can('deleteGroup', $topic),
            'can_leave_group' => $request->user()->can('leaveGroup', $topic),
            'group_image_url' => $topic->groupImageUrl(),
        ]);
    }

    public function store(Request $request, DiscussionTopic $topic): JsonResponse
    {
        $this->authorize('manageMembers', $topic);

        $data = $request->validate([
            'member_ids' => ['required', 'array', 'min:1'],
            'member_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('is_blocked', false)->where('is_chat_blocked', false)),
            ],
        ]);

        $memberIds = collect($data['member_ids'])
            ->map(fn ($id) => (int) $id)
            ->reject(fn (int $id) => $id === (int) $request->user()->id)
            ->values()
            ->all();

        $added = $topic->addGroupMembers($memberIds);

        return response()->json([
            'message' => count($added) > 0 ? 'Members added.' : 'Selected members are already in this group.',
            'added_ids' => $added,
            'members' => $this->onlineService->membersWithOnlineStatus($topic->fresh(['members', 'user'])),
        ]);
    }

    public function destroy(Request $request, DiscussionTopic $topic, User $member): JsonResponse
    {
        $this->authorize('manageMembers', $topic);

        abort_unless($topic->isGroupContainer(), 404);

        if (! $topic->removeGroupMember((int) $member->id)) {
            return response()->json([
                'message' => 'This member cannot be removed from the group.',
            ], 422);
        }

        return response()->json([
            'message' => 'Member removed.',
            'members' => $this->onlineService->membersWithOnlineStatus($topic->fresh(['members', 'user'])),
        ]);
    }

    public function leave(Request $request, DiscussionTopic $topic): JsonResponse
    {
        $this->authorize('leaveGroup', $topic);

        abort_unless($topic->isGroupContainer(), 404);

        if (! $topic->leaveGroup($request->user())) {
            return response()->json([
                'message' => 'You cannot leave this group.',
            ], 422);
        }

        return response()->json([
            'message' => 'You left the group.',
            'left_group_id' => $topic->id,
        ]);
    }
}
