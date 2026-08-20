<?php

namespace App\Http\Controllers\Discussion;

use App\Http\Controllers\Controller;
use App\Models\DiscussionGroupInvitation;
use App\Models\DiscussionTopic;
use App\Models\User;
use App\Services\DiscussionGroupInvitationService;
use App\Services\DiscussionOnlineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiscussionMemberController extends Controller
{
    public function __construct(
        private DiscussionOnlineService $onlineService,
        private DiscussionGroupInvitationService $invitationService,
    ) {}

    public function searchUsers(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $digits = preg_replace('/\D+/', '', (string) ($data['q'] ?? '')) ?? '';

        if (strlen($digits) < 8) {
            return response()->json([
                'users' => [],
                'message' => $digits === ''
                    ? 'Enter a mobile number to find a member.'
                    : 'Enter a complete mobile number to search.',
            ]);
        }

        $lastTen = strlen($digits) > 10 ? substr($digits, -10) : $digits;

        $users = User::query()
            ->where('id', '!=', $request->user()->id)
            ->where('is_blocked', false)
            ->where('is_chat_blocked', false)
            ->where(function ($builder) use ($digits, $lastTen): void {
                $builder->where('phone_number', $digits)
                    ->orWhere('phone_number', 'like', '%'.$lastTen)
                    ->orWhere('whatsapp_number', $digits)
                    ->orWhere('whatsapp_number', 'like', '%'.$lastTen);
            })
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name', 'full_name', 'email', 'phone_number']);

        return response()->json([
            'users' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->authorDisplayName(),
                'email' => $user->email,
                'phone' => $user->phone_number,
                'initials' => $user->authorInitials(),
            ])->values(),
        ]);
    }

    public function index(Request $request, DiscussionTopic $topic): JsonResponse
    {
        $this->authorize('view', $topic);

        abort_unless($topic->isGroupContainer(), 404);

        $canManage = $request->user()->can('manageMembers', $topic);

        return response()->json([
            'members' => $this->onlineService->membersWithOnlineStatus($topic),
            'online_users' => $this->onlineService->onlineUsersForTopic($topic),
            'pending_invitations' => $canManage ? $this->pendingInvitationsForTopic($topic) : [],
            'can_manage_members' => $canManage,
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

        $invited = $this->invitationService->inviteMembers($topic, $request->user(), $memberIds);
        $invitedCount = count($invited);

        return response()->json([
            'message' => $invitedCount > 0
                ? ($invitedCount === 1
                    ? 'Invitation sent. They will join after they approve.'
                    : 'Invitations sent. They will join after they approve.')
                : 'Selected members are already in this group or already invited.',
            'invited_ids' => collect($invited)->pluck('invitee_id')->values()->all(),
            'pending_invitations' => $this->pendingInvitationsForTopic($topic),
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

    /**
     * @return list<array<string, mixed>>
     */
    private function pendingInvitationsForTopic(DiscussionTopic $topic): array
    {
        return $topic->invitations()
            ->pending()
            ->with(['inviter', 'invitee', 'topic'])
            ->latest()
            ->get()
            ->map(fn (DiscussionGroupInvitation $invitation) => $invitation->toBroadcastArray())
            ->values()
            ->all();
    }
}
