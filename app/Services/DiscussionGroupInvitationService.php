<?php

namespace App\Services;

use App\Mail\DiscussionGroupInvitationMail;
use App\Mail\DiscussionGroupInvitationResponseMail;
use App\Models\DiscussionGroupInvitation;
use App\Models\DiscussionTopic;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

class DiscussionGroupInvitationService
{
    private const REGISTERED_INVITE_TEMPLATE_ID = '1707177571854887444';

    private const UNREGISTERED_INVITE_TEMPLATE_ID = '1707177571854887445';

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
            ->whereNotNull('invitee_id')
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

            $pendingPhoneInvitation = $topic->invitations()
                ->pending()
                ->whereNull('invitee_id')
                ->whereNotNull('invitee_phone')
                ->get()
                ->first(fn (DiscussionGroupInvitation $invitation) => $invitation->isInvitee($invitee));

            if ($pendingPhoneInvitation) {
                $this->claimInvitationForUser($pendingPhoneInvitation, $invitee);

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

    /**
     * @param  list<string>  $phoneNumbers
     * @return list<DiscussionGroupInvitation>
     */
    public function inviteByPhoneNumbers(DiscussionTopic $topic, User $inviter, array $phoneNumbers): array
    {
        $created = [];
        $memberIds = [];

        foreach (collect($phoneNumbers)
            ->map(fn ($phone) => DiscussionGroupInvitation::normalizePhone($phone))
            ->filter()
            ->unique()
            ->values() as $phone) {
            $existingUser = $this->findUserByPhone($phone);

            if ($existingUser) {
                $memberIds[] = (int) $existingUser->id;

                continue;
            }

            if ($this->hasPendingPhoneInvitation($topic, $phone)) {
                continue;
            }

            $invitation = $topic->invitations()->create([
                'inviter_id' => $inviter->id,
                'invitee_id' => null,
                'invitee_phone' => $phone,
                'token' => Str::random(48),
                'status' => DiscussionGroupInvitation::STATUS_PENDING,
            ]);

            $this->notifyUnregisteredPhoneInvitee($invitation->fresh(['topic', 'inviter']));
            $created[] = $invitation;
        }

        if ($memberIds !== []) {
            $created = array_merge($created, $this->inviteMembers($topic, $inviter, $memberIds));
        }

        return $created;
    }

    public function claimPendingInvitationsForUser(User $user): void
    {
        $phone = DiscussionGroupInvitation::normalizePhone($user->phone_number);

        if ($phone === null) {
            return;
        }

        $invitations = DiscussionGroupInvitation::query()
            ->pending()
            ->whereNull('invitee_id')
            ->whereNotNull('invitee_phone')
            ->get()
            ->filter(fn (DiscussionGroupInvitation $invitation) => DiscussionGroupInvitation::phonesMatch(
                $invitation->invitee_phone,
                $phone
            ) || DiscussionGroupInvitation::phonesMatch($invitation->invitee_phone, $user->whatsapp_number));

        foreach ($invitations as $invitation) {
            $this->claimInvitationForUser($invitation, $user);
        }
    }

    public function claimInvitationForUser(DiscussionGroupInvitation $invitation, User $user): DiscussionGroupInvitation
    {
        if ((int) $invitation->invitee_id === (int) $user->id) {
            return $invitation;
        }

        if ($invitation->invitee_id !== null || ! $invitation->isPhoneInvite() || ! $invitation->isInvitee($user)) {
            return $invitation;
        }

        $invitation->update([
            'invitee_id' => $user->id,
        ]);

        $invitation = $invitation->fresh(['topic', 'inviter', 'invitee']);
        $this->notifyClaimedInvitee($invitation);

        return $invitation;
    }

    public function accept(DiscussionGroupInvitation $invitation, ?User $user = null): DiscussionGroupInvitation
    {
        if ($user) {
            $invitation = $this->claimInvitationForUser($invitation, $user);
        }

        $invitation = $this->markStatus($invitation, DiscussionGroupInvitation::STATUS_ACCEPTED);
        $invitation->topic?->addGroupMembers([(int) $invitation->invitee_id]);
        $this->notifyInviter($invitation);

        return $invitation;
    }

    public function reject(DiscussionGroupInvitation $invitation, ?User $user = null): DiscussionGroupInvitation
    {
        if ($user) {
            $invitation = $this->claimInvitationForUser($invitation, $user);
        }

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
        $url = $invitation->invitationUrl();

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
            $smsMessage = sprintf(
                '%s has added you to a group. Please click the link below to approve or decline the group invitation: %s Do not share this link with anyone. – Annuvedant Team',
                $inviterName,
                $url
            );

            $sent = $this->smsService->send(
                $invitee->phone_number,
                $smsMessage,
                self::REGISTERED_INVITE_TEMPLATE_ID
            );

            if (! $sent) {
                Log::warning('Group invitation SMS failed', [
                    'invitation_id' => $invitation->id,
                    'phone' => $invitee->phone_number,
                ]);
            }
        }
    }

    private function notifyUnregisteredPhoneInvitee(DiscussionGroupInvitation $invitation): void
    {
        $inviterName = $invitation->inviter?->authorDisplayName() ?: 'A community member';
        $invitationUrl = $invitation->invitationUrl();
        $smsMessage = sprintf(
            '%s has added you to a group. If you are not registered with SoilnWater, please register first and then click the link below to approve or decline the group invitation: %s – Annuvedant Team',
            $inviterName,
            $invitationUrl
        );

        $sent = $this->smsService->send(
            (string) $invitation->invitee_phone,
            $smsMessage,
            self::UNREGISTERED_INVITE_TEMPLATE_ID
        );

        if (! $sent) {
            Log::warning('Unregistered group invitation SMS failed', [
                'invitation_id' => $invitation->id,
                'phone' => $invitation->invitee_phone,
            ]);
        }
    }

    private function notifyClaimedInvitee(DiscussionGroupInvitation $invitation): void
    {
        $invitee = $invitation->invitee;

        if (! $invitee) {
            return;
        }

        $inviterName = $invitation->inviter?->authorDisplayName() ?: 'A community member';
        $groupTitle = $invitation->topic?->title ?: 'a community group';

        PortalNotificationService::notifyUser(
            $invitee,
            'Group invitation',
            $inviterName.' invited you to join "'.$groupTitle.'". Approve or reject the invitation.',
            $invitation->invitationUrl(),
            'group_invite',
            [
                'invitation_id' => $invitation->id,
                'accept_url' => route('discussions.invitations.accept', $invitation),
                'reject_url' => route('discussions.invitations.reject', $invitation),
            ]
        );
    }

    private function notifyInviter(DiscussionGroupInvitation $invitation): void
    {
        $inviter = $invitation->inviter;
        $inviteeName = $invitation->invitee?->authorDisplayName()
            ?: ($invitation->invitee_phone ?: 'A community member');
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

    private function findUserByPhone(string $phone): ?User
    {
        $lastTen = strlen($phone) > 10 ? substr($phone, -10) : $phone;

        return User::query()
            ->where('is_blocked', false)
            ->where('is_chat_blocked', false)
            ->where(function ($builder) use ($phone, $lastTen): void {
                $builder->where('phone_number', $phone)
                    ->orWhere('phone_number', 'like', '%'.$lastTen)
                    ->orWhere('whatsapp_number', $phone)
                    ->orWhere('whatsapp_number', 'like', '%'.$lastTen);
            })
            ->first();
    }

    private function hasPendingPhoneInvitation(DiscussionTopic $topic, string $phone): bool
    {
        return $topic->invitations()
            ->pending()
            ->whereNotNull('invitee_phone')
            ->get()
            ->contains(fn (DiscussionGroupInvitation $invitation) => DiscussionGroupInvitation::phonesMatch(
                $invitation->invitee_phone,
                $phone
            ));
    }
}
