<?php

namespace App\Services;

use App\Mail\DiscussionGroupInvitationMail;
use App\Mail\DiscussionGroupInvitationResponseMail;
use App\Models\DiscussionGroupInvitation;
use App\Models\DiscussionTopic;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DiscussionGroupInvitationService
{
    public function __construct(private MessageIndiaSmsService $smsService) {}

    /**
     * @param  list<int>  $memberIds
     * @return list<DiscussionGroupInvitation>
     */
    public function inviteMembers(DiscussionTopic $topic, User $inviter, array $memberIds): array
    {
        $inviteeIds = collect($memberIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->reject(fn (int $id) => $id === (int) $inviter->id)
            ->values();

        if ($inviteeIds->isEmpty()) {
            return [];
        }

        $existingMemberIds = $topic->members()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        $pendingInviteeIds = $topic->invitations()
            ->pending()
            ->pluck('invitee_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $users = User::query()
            ->whereIn('id', $inviteeIds->all())
            ->where('is_blocked', false)
            ->where('is_chat_blocked', false)
            ->get();

        $created = [];

        foreach ($users as $invitee) {
            $inviteeId = (int) $invitee->id;

            if (in_array($inviteeId, $existingMemberIds, true) || in_array($inviteeId, $pendingInviteeIds, true)) {
                continue;
            }

            $invitation = $topic->invitations()->create([
                'inviter_id' => $inviter->id,
                'invitee_id' => $inviteeId,
                'status' => DiscussionGroupInvitation::STATUS_PENDING,
            ]);

            $this->notifyInvitee($invitation->fresh(['topic', 'inviter', 'invitee']));
            $created[] = $invitation;
            $pendingInviteeIds[] = $inviteeId;
        }

        return $created;
    }

    public function accept(DiscussionGroupInvitation $invitation): DiscussionGroupInvitation
    {
        $invitation = $this->markStatus($invitation, DiscussionGroupInvitation::STATUS_ACCEPTED);
        $invitation->topic?->addGroupMembers([(int) $invitation->invitee_id]);
        $this->notifyInviter($invitation);

        return $invitation;
    }

    public function reject(DiscussionGroupInvitation $invitation): DiscussionGroupInvitation
    {
        $invitation = $this->markStatus($invitation, DiscussionGroupInvitation::STATUS_REJECTED);
        $this->notifyInviter($invitation);

        return $invitation;
    }

    private function markStatus(DiscussionGroupInvitation $invitation, string $status): DiscussionGroupInvitation
    {
        $invitation->update([
            'status' => $status,
            'responded_at' => now(),
        ]);

        return $invitation->fresh(['topic', 'inviter', 'invitee']);
    }

    private function notifyInvitee(DiscussionGroupInvitation $invitation): void
    {
        $invitee = $invitation->invitee;
        $inviterName = $invitation->inviter?->authorDisplayName() ?: 'A community member';
        $groupTitle = $invitation->topic?->title ?: 'a community group';
        $url = route('discussions.invitations.show', $invitation);

        PortalNotificationService::notifyUser(
            $invitee,
            'Group invitation',
            $inviterName.' invited you to join "'.$groupTitle.'". Approve or reject the invitation.',
            $url,
            'group_invite',
            [
                'invitation_id' => $invitation->id,
                'accept_url' => route('discussions.invitations.accept', $invitation),
                'reject_url' => route('discussions.invitations.reject', $invitation),
            ]
        );

        if ($invitee?->email) {
            try {
                Mail::to($invitee->email)->send(new DiscussionGroupInvitationMail($invitation));
            } catch (Throwable $exception) {
                Log::error('Group invitation email failed', [
                    'invitation_id' => $invitation->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        if ($invitee?->phone_number) {
            $this->smsService->send(
                $invitee->phone_number,
                sprintf(
                    'Hello %s, %s invited you to join the group "%s" on SoilnWater. Open the portal to approve or reject. – Annuvedant Team',
                    $invitee->authorDisplayName(),
                    $inviterName,
                    $groupTitle
                ),
                config('services.message.group_invite_templateid')
            );
        }
    }

    private function notifyInviter(DiscussionGroupInvitation $invitation): void
    {
        $inviter = $invitation->inviter;
        $inviteeName = $invitation->invitee?->authorDisplayName() ?: 'A community member';
        $groupTitle = $invitation->topic?->title ?: 'your group';
        $accepted = $invitation->status === DiscussionGroupInvitation::STATUS_ACCEPTED;
        $action = $accepted ? 'accepted' : 'declined';
        $url = $accepted && $invitation->topic
            ? route('discussions.messenger', $invitation->topic)
            : route('notifications.index');

        PortalNotificationService::notifyUser(
            $inviter,
            'Group invitation '.$action,
            $inviteeName.' '.$action.' your invitation to join "'.$groupTitle.'".',
            $url,
            'group_invite_response'
        );

        if ($inviter?->email) {
            try {
                Mail::to($inviter->email)->send(new DiscussionGroupInvitationResponseMail($invitation));
            } catch (Throwable $exception) {
                Log::error('Group invitation response email failed', [
                    'invitation_id' => $invitation->id,
                    'message' => $exception->getMessage(),
                ]);
            }
        }
    }
}
