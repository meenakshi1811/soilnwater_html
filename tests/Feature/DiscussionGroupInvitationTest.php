<?php

namespace Tests\Feature;

use App\Mail\DiscussionGroupInvitationMail;
use App\Mail\DiscussionGroupInvitationResponseMail;
use App\Models\DiscussionGroupInvitation;
use App\Models\DiscussionTopic;
use App\Models\User;
use App\Notifications\PortalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DiscussionGroupInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_search_requires_a_phone_number_and_does_not_list_everyone(): void
    {
        $actor = User::factory()->create(['email_verified_at' => now()]);
        $match = User::factory()->create([
            'email_verified_at' => now(),
            'phone_number' => '9876543210',
            'name' => 'Priya Sharma',
        ]);
        User::factory()->create([
            'email_verified_at' => now(),
            'phone_number' => '9123456780',
            'name' => 'Hidden Contact',
        ]);

        $this->actingAs($actor)
            ->getJson(route('discussions.users.search'))
            ->assertOk()
            ->assertJsonCount(0, 'users')
            ->assertJsonPath('message', 'Enter a mobile number to find a member.');

        $this->actingAs($actor)
            ->getJson(route('discussions.users.search', ['q' => '9876543210']))
            ->assertOk()
            ->assertJsonCount(1, 'users')
            ->assertJsonPath('users.0.id', $match->id)
            ->assertJsonPath('users.0.phone', '9876543210');
    }

    public function test_creating_a_group_sends_invitations_instead_of_adding_members(): void
    {
        Mail::fake();
        Notification::fake();

        $creator = User::factory()->create(['email_verified_at' => now()]);
        $invitee = User::factory()->create([
            'email_verified_at' => now(),
            'phone_number' => '9988776655',
        ]);

        $this->actingAs($creator)
            ->postJson(route('discussions.store'), [
                'title' => 'Farmers circle',
                'is_group' => true,
                'member_ids' => [$invitee->id],
            ])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Group created. Invitations sent — members join after they approve.']);

        $topic = DiscussionTopic::query()->firstOrFail();
        $this->assertTrue($topic->canAccess($creator));
        $this->assertFalse($topic->canAccess($invitee));
        $this->assertDatabaseHas('discussion_group_invitations', [
            'discussion_topic_id' => $topic->id,
            'inviter_id' => $creator->id,
            'invitee_id' => $invitee->id,
            'status' => DiscussionGroupInvitation::STATUS_PENDING,
        ]);

        Mail::assertSent(DiscussionGroupInvitationMail::class, fn ($mail) => $mail->hasTo($invitee->email));
        Notification::assertSentTo($invitee, PortalNotification::class);
    }

    public function test_invitee_can_approve_and_join_the_group(): void
    {
        Mail::fake();
        Notification::fake();

        [$creator, $invitee, $invitation] = $this->createPendingInvitation();

        $this->actingAs($invitee)
            ->postJson(route('discussions.invitations.accept', $invitation))
            ->assertOk()
            ->assertJsonFragment(['message' => 'You joined the group.']);

        $this->assertTrue($invitation->topic->fresh()->canAccess($invitee));
        $this->assertSame(DiscussionGroupInvitation::STATUS_ACCEPTED, $invitation->fresh()->status);

        Mail::assertSent(DiscussionGroupInvitationResponseMail::class, fn ($mail) => $mail->hasTo($creator->email));
        Notification::assertSentTo($creator, PortalNotification::class);
    }

    public function test_invitee_can_reject_and_inviter_is_notified(): void
    {
        Mail::fake();
        Notification::fake();

        [$creator, $invitee, $invitation] = $this->createPendingInvitation();

        $this->actingAs($invitee)
            ->postJson(route('discussions.invitations.reject', $invitation))
            ->assertOk()
            ->assertJsonFragment(['message' => 'You declined the group invitation.']);

        $this->assertFalse($invitation->topic->fresh()->canAccess($invitee));
        $this->assertSame(DiscussionGroupInvitation::STATUS_REJECTED, $invitation->fresh()->status);

        Mail::assertSent(DiscussionGroupInvitationResponseMail::class, fn ($mail) => $mail->hasTo($creator->email));
        Notification::assertSentTo($creator, PortalNotification::class);
    }

    public function test_outsider_cannot_accept_someone_elses_invitation(): void
    {
        [, , $invitation] = $this->createPendingInvitation();
        $outsider = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($outsider)
            ->postJson(route('discussions.invitations.accept', $invitation))
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: User, 2: DiscussionGroupInvitation}
     */
    private function createPendingInvitation(): array
    {
        $creator = User::factory()->create(['email_verified_at' => now()]);
        $invitee = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($creator)->postJson(route('discussions.store'), [
            'title' => 'Invite group',
            'is_group' => true,
            'member_ids' => [$invitee->id],
        ])->assertOk();

        $invitation = DiscussionGroupInvitation::query()->firstOrFail();

        return [$creator, $invitee, $invitation];
    }
}
