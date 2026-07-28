<?php

namespace Tests\Feature;

use App\Events\Discussion\ReactionUpdated;
use App\Events\Discussion\ReplyCreated;
use App\Events\Discussion\TopicCreated;
use App\Events\Discussion\TopicPinned;
use App\Models\DiscussionReply;
use App\Models\DiscussionTopic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DiscussionModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_messenger_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('discussions.messenger'))
            ->assertOk()
            ->assertSee('discussion-widget--standalone', false)
            ->assertSee('Search or start new chat');
    }

    public function test_guest_cannot_access_discussions(): void
    {
        $this->get(route('discussions.index'))->assertRedirect(route('login'));
    }

    public function test_verified_user_can_view_discussion_index(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('discussions.index'))
            ->assertOk()
            ->assertSee('Discussions');
    }

    public function test_verified_user_can_create_topic(): void
    {
        Event::fake([TopicCreated::class]);

        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('discussions.store'), [
            'title' => 'Soil health tips',
            'body' => 'What practices work best in clay soil?',
        ]);

        $response->assertOk()->assertJsonFragment(['message' => 'Topic created.']);

        $topic = DiscussionTopic::query()->firstOrFail();
        $this->assertSame($user->id, $topic->user_id);
        $this->assertSame('Soil health tips', $topic->title);

        Event::assertDispatched(TopicCreated::class);
    }

    public function test_verified_user_can_reply_to_topic(): void
    {
        Event::fake([ReplyCreated::class]);

        $author = User::factory()->create(['email_verified_at' => now()]);
        $replier = User::factory()->create(['email_verified_at' => now()]);
        $topic = DiscussionTopic::factory()->create(['user_id' => $author->id]);

        $response = $this->actingAs($replier)->postJson(route('discussions.replies.store', $topic), [
            'body' => 'Try cover cropping between seasons.',
        ]);

        $response->assertOk()->assertJsonFragment(['message' => 'Reply posted.']);

        $reply = DiscussionReply::query()->firstOrFail();
        $this->assertSame($topic->id, $reply->discussion_topic_id);
        $this->assertSame($replier->id, $reply->user_id);
        $this->assertSame(1, $topic->fresh()->replies_count);

        Event::assertDispatched(ReplyCreated::class);
    }

    public function test_non_admin_cannot_pin_topic(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'user',
        ]);
        $topic = DiscussionTopic::factory()->create();

        $this->actingAs($user)
            ->postJson(route('discussions.pin', $topic))
            ->assertForbidden();
    }

    public function test_admin_can_pin_and_unpin_topic(): void
    {
        Event::fake([TopicPinned::class]);

        $admin = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);
        $topic = DiscussionTopic::factory()->create();

        $this->actingAs($admin)
            ->postJson(route('discussions.pin', $topic))
            ->assertOk()
            ->assertJsonFragment(['is_pinned' => true]);

        $this->assertTrue($topic->fresh()->is_pinned);
        $this->assertSame($admin->id, $topic->fresh()->pinned_by);

        Event::assertDispatched(TopicPinned::class);

        $this->actingAs($admin)
            ->postJson(route('discussions.pin', $topic->fresh()))
            ->assertOk()
            ->assertJsonFragment(['is_pinned' => false]);

        $this->assertFalse($topic->fresh()->is_pinned);
    }

    public function test_user_can_toggle_reaction_on_topic(): void
    {
        Event::fake([ReactionUpdated::class]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $topic = DiscussionTopic::factory()->create();

        $this->actingAs($user)
            ->postJson(route('discussions.react', $topic), ['reaction' => 'Like'])
            ->assertOk()
            ->assertJsonFragment(['active' => true, 'reaction' => 'Like']);

        $this->assertDatabaseHas('discussion_reactions', [
            'reactable_type' => DiscussionTopic::class,
            'reactable_id' => $topic->id,
            'user_id' => $user->id,
            'reaction' => 'Like',
        ]);

        Event::assertDispatched(ReactionUpdated::class);

        $this->actingAs($user)
            ->postJson(route('discussions.react', $topic), ['reaction' => 'Like'])
            ->assertOk()
            ->assertJsonFragment(['active' => false]);

        $this->assertDatabaseMissing('discussion_reactions', [
            'reactable_type' => DiscussionTopic::class,
            'reactable_id' => $topic->id,
            'user_id' => $user->id,
            'reaction' => 'Like',
        ]);
    }

    public function test_user_can_toggle_reaction_on_reply(): void
    {
        Event::fake([ReactionUpdated::class]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $topic = DiscussionTopic::factory()->create();
        $reply = DiscussionReply::query()->create([
            'discussion_topic_id' => $topic->id,
            'user_id' => $user->id,
            'body' => 'Great point.',
        ]);

        $this->actingAs($user)
            ->postJson(route('discussions.replies.react', $reply), ['reaction' => 'Agree'])
            ->assertOk()
            ->assertJsonFragment(['active' => true, 'reaction' => 'Agree']);

        Event::assertDispatched(ReactionUpdated::class);
    }

    public function test_verified_user_can_fetch_topics_as_json_for_widget(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $topic = DiscussionTopic::factory()->create([
            'user_id' => $user->id,
            'title' => 'Widget topic',
        ]);

        $this->actingAs($user)
            ->getJson(route('discussions.index'))
            ->assertOk()
            ->assertJsonPath('topics.0.id', $topic->id)
            ->assertJsonPath('topics.0.title', 'Widget topic')
            ->assertJsonPath('topics.0.created_at_date', $topic->created_at->format('d M Y'))
            ->assertJsonStructure(['topics', 'can_pin', 'global_unread', 'meta']);
    }

    public function test_unread_summary_and_mark_read(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);
        $reader = User::factory()->create(['email_verified_at' => now()]);
        $topic = DiscussionTopic::factory()->create([
            'user_id' => $author->id,
            'title' => 'Unread topic',
            'body' => 'Hello everyone',
        ]);

        DiscussionReply::query()->create([
            'discussion_topic_id' => $topic->id,
            'user_id' => $author->id,
            'body' => 'Follow-up message',
        ]);

        $this->actingAs($reader)
            ->getJson(route('discussions.unread-summary'))
            ->assertOk()
            ->assertJsonPath('global_unread', 2)
            ->assertJsonPath("topics.{$topic->id}", 2);

        $this->actingAs($reader)
            ->getJson(route('discussions.show', $topic))
            ->assertOk();

        $this->actingAs($reader)
            ->getJson(route('discussions.unread-summary'))
            ->assertOk()
            ->assertJsonPath('global_unread', 0)
            ->assertJsonPath("topics.{$topic->id}", 0);
    }

    public function test_reply_requires_body_or_attachment(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $topic = DiscussionTopic::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->postJson(route('discussions.replies.store', $topic), [])
            ->assertUnprocessable();
    }

    public function test_verified_user_can_fetch_topic_thread_as_json_for_widget(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $topic = DiscussionTopic::factory()->create([
            'user_id' => $user->id,
            'title' => 'Thread topic',
            'body' => 'Opening post',
        ]);

        DiscussionReply::query()->create([
            'discussion_topic_id' => $topic->id,
            'user_id' => $user->id,
            'body' => 'First reply',
        ]);

        $this->actingAs($user)
            ->getJson(route('discussions.show', $topic))
            ->assertOk()
            ->assertJsonPath('topic.id', $topic->id)
            ->assertJsonPath('topic.title', 'Thread topic')
            ->assertJsonPath('topic.replies.0.body', 'First reply')
            ->assertJsonStructure(['topic' => ['replies', 'user_reactions'], 'can_pin']);
    }

    public function test_group_chat_is_only_visible_to_creator_and_members(): void
    {
        $creator = User::factory()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now()]);
        $outsider = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($creator)->postJson(route('discussions.store'), [
            'title' => 'Private group',
            'body' => 'Hello team',
            'is_group' => true,
            'member_ids' => [$member->id],
        ])->assertOk();

        $topic = DiscussionTopic::query()->firstOrFail();
        $this->assertTrue($topic->is_group);
        $this->assertTrue($topic->canAccess($creator));
        $this->assertTrue($topic->canAccess($member));
        $this->assertFalse($topic->canAccess($outsider));

        $this->actingAs($creator)
            ->getJson(route('discussions.index'))
            ->assertOk()
            ->assertJsonPath('topics.0.id', $topic->id);

        $this->actingAs($member)
            ->getJson(route('discussions.index'))
            ->assertOk()
            ->assertJsonPath('topics.0.id', $topic->id);

        $this->actingAs($outsider)
            ->getJson(route('discussions.index'))
            ->assertOk()
            ->assertJsonCount(0, 'topics');

        $this->actingAs($outsider)
            ->getJson(route('discussions.show', $topic))
            ->assertForbidden();

        $this->actingAs($member)
            ->postJson(route('discussions.replies.store', $topic), [
                'body' => 'Member reply',
            ])
            ->assertOk();

        $this->actingAs($outsider)
            ->postJson(route('discussions.replies.store', $topic), [
                'body' => 'Should fail',
            ])
            ->assertForbidden();
    }

    public function test_group_creator_can_add_members(): void
    {
        $creator = User::factory()->create(['email_verified_at' => now()]);
        $existingMember = User::factory()->create(['email_verified_at' => now()]);
        $newMember = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($creator)->postJson(route('discussions.store'), [
            'title' => 'Operations group',
            'is_group' => true,
            'member_ids' => [$existingMember->id],
        ])->assertOk();

        $topic = DiscussionTopic::query()->firstOrFail();

        $this->actingAs($creator)
            ->postJson(route('discussions.members.store', $topic), [
                'member_ids' => [$newMember->id],
            ])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Members added.']);

        $this->assertTrue($topic->fresh()->canAccess($newMember));

        $this->actingAs($existingMember)
            ->postJson(route('discussions.members.store', $topic), [
                'member_ids' => [User::factory()->create(['email_verified_at' => now()])->id],
            ])
            ->assertForbidden();
    }

    public function test_group_creator_can_remove_members_and_manage_group_image(): void
    {
        $creator = User::factory()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($creator)->postJson(route('discussions.store'), [
            'title' => 'Photo group',
            'is_group' => true,
            'member_ids' => [$member->id],
        ])->assertOk();

        $topic = DiscussionTopic::query()->firstOrFail();

        $this->actingAs($creator)
            ->postJson(route('discussions.group-image.update', $topic), [
                'group_image' => \Illuminate\Http\UploadedFile::fake()->image('group.jpg'),
            ])
            ->assertOk()
            ->assertJsonPath('group_image_url', fn ($url) => is_string($url) && $url !== '');

        $this->assertNotNull($topic->fresh()->group_image);

        $this->actingAs($creator)
            ->deleteJson(route('discussions.group-image.destroy', $topic))
            ->assertOk()
            ->assertJsonPath('group_image_url', null);

        $this->assertNull($topic->fresh()->group_image);

        $this->actingAs($creator)
            ->deleteJson(route('discussions.members.destroy', ['topic' => $topic, 'member' => $member]))
            ->assertOk()
            ->assertJsonFragment(['message' => 'Member removed.']);

        $this->assertFalse($topic->fresh()->canAccess($member));

        $this->actingAs($member)
            ->deleteJson(route('discussions.members.destroy', ['topic' => $topic, 'member' => $member]))
            ->assertForbidden();
    }

    public function test_group_member_can_create_topic_under_group(): void
    {
        $creator = User::factory()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now()]);
        $outsider = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($creator)->postJson(route('discussions.store'), [
            'title' => 'Planning group',
            'is_group' => true,
            'member_ids' => [$member->id],
        ])->assertOk();

        $group = DiscussionTopic::query()->firstOrFail();

        $this->actingAs($member)
            ->postJson(route('discussions.store'), [
                'title' => 'Irrigation ideas',
                'body' => 'Share your methods',
                'parent_topic_id' => $group->id,
            ])
            ->assertOk()
            ->assertJsonPath('topic.parent_topic_id', $group->id);

        $child = DiscussionTopic::query()->where('parent_topic_id', $group->id)->firstOrFail();
        $this->assertFalse($child->is_group);
        $this->assertTrue($child->canAccess($member));

        $this->actingAs($member)
            ->getJson(route('discussions.show', $group))
            ->assertOk()
            ->assertJsonPath('topic.children.0.id', $child->id);

        $this->actingAs($outsider)
            ->postJson(route('discussions.store'), [
                'title' => 'Blocked topic',
                'parent_topic_id' => $group->id,
            ])
            ->assertForbidden();
    }
}
