<?php

namespace Tests\Feature;

use App\Models\DiscussionTopic;
use App\Models\FoulWord;
use App\Models\User;
use App\Services\FoulWordFilter;
use Database\Seeders\FoulWordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityChatModerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        FoulWordFilter::forgetCache();
    }

    public function test_admin_can_view_all_community_chats(): void
    {
        $admin = $this->admin();
        $author = User::factory()->create(['email_verified_at' => now()]);
        $topic = DiscussionTopic::factory()->create([
            'user_id' => $author->id,
            'title' => 'Farm water chat',
            'body' => 'How do you store rainwater?',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.community-chats.index'))
            ->assertOk()
            ->assertSee('All Chats');

        $this->actingAs($admin)
            ->get(route('admin.community-chats.show', $topic))
            ->assertOk()
            ->assertSee('Farm water chat')
            ->assertSee('How do you store rainwater?');
    }

    public function test_non_admin_cannot_view_community_chat_admin_pages(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
        $topic = DiscussionTopic::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('admin.community-chats.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(route('admin.community-chats.show', $topic))
            ->assertForbidden();
    }

    public function test_admin_can_block_user_from_chat_and_blocked_user_cannot_access_chat(): void
    {
        $admin = $this->admin();
        $user = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
        $topic = DiscussionTopic::factory()->create(['user_id' => $user->id]);

        $this->actingAs($admin)
            ->patchJson(route('admin.community-chats.users.toggle-block', $user))
            ->assertOk()
            ->assertJsonPath('is_chat_blocked', true);

        $this->assertTrue($user->fresh()->isChatBlocked());

        $this->actingAs($user)
            ->get(route('discussions.messenger'))
            ->assertForbidden();

        $this->actingAs($user)
            ->postJson(route('discussions.replies.store', $topic), [
                'body' => 'This should not go through.',
            ])
            ->assertForbidden()
            ->assertJsonFragment(['message' => 'Your chat access has been blocked by the admin.']);
    }

    public function test_admin_cannot_block_another_admin_from_chat(): void
    {
        $admin = $this->admin();
        $otherAdmin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patchJson(route('admin.community-chats.users.toggle-block', $otherAdmin))
            ->assertUnprocessable()
            ->assertJsonFragment(['message' => 'Admin accounts cannot be blocked from chat.']);
    }

    public function test_admin_can_crud_foul_words(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->postJson(route('admin.foul-words.store'), [
                'word' => 'BadWord',
                'is_active' => true,
            ])
            ->assertOk()
            ->assertJsonFragment(['message' => 'Foul word added successfully.']);

        $word = FoulWord::query()->firstOrFail();
        $this->assertSame('badword', $word->word);

        $this->actingAs($admin)
            ->putJson(route('admin.foul-words.update', $word), [
                'word' => 'worseword',
                'is_active' => true,
            ])
            ->assertOk();

        $this->assertSame('worseword', $word->fresh()->word);

        $this->actingAs($admin)
            ->patchJson(route('admin.foul-words.toggle-status', $word))
            ->assertOk()
            ->assertJsonPath('is_active', false);

        $this->actingAs($admin)
            ->deleteJson(route('admin.foul-words.destroy', $word))
            ->assertOk();

        $this->assertDatabaseMissing('foul_words', ['id' => $word->id]);
    }

    public function test_reply_with_foul_word_is_rejected(): void
    {
        FoulWord::query()->create(['word' => 'fuck', 'is_active' => true]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $topic = DiscussionTopic::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->postJson(route('discussions.replies.store', $topic), [
                'body' => 'This is a fuck message',
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['You have used the foul word.']);

        $this->assertDatabaseCount('discussion_replies', 0);
    }

    public function test_topic_with_foul_word_in_title_is_rejected(): void
    {
        FoulWord::query()->create(['word' => 'shit', 'is_active' => true]);

        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->postJson(route('discussions.store'), [
                'title' => 'This shit idea',
                'body' => 'Please help',
            ])
            ->assertUnprocessable()
            ->assertJsonFragment(['You have used the foul word.']);

        $this->assertDatabaseCount('discussion_topics', 0);
    }

    public function test_inactive_foul_word_does_not_block_messages(): void
    {
        FoulWord::query()->create(['word' => 'fuck', 'is_active' => false]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $topic = DiscussionTopic::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->postJson(route('discussions.replies.store', $topic), [
                'body' => 'This is a fuck message',
            ])
            ->assertOk();

        $this->assertDatabaseHas('discussion_replies', [
            'discussion_topic_id' => $topic->id,
            'body' => 'This is a fuck message',
        ]);
    }

    public function test_foul_word_seeder_creates_active_words(): void
    {
        $this->seed(FoulWordSeeder::class);

        $this->assertTrue(FoulWord::query()->where('word', 'fuck')->where('is_active', true)->exists());
        $this->assertTrue(FoulWord::query()->where('word', 'madarchod')->where('is_active', true)->exists());
        $this->assertGreaterThan(10, FoulWord::query()->count());
    }

    public function test_foul_word_filter_matches_whole_words_only(): void
    {
        FoulWord::query()->create(['word' => 'ass', 'is_active' => true]);

        $filter = app(FoulWordFilter::class);

        $this->assertTrue($filter->contains('You are an ass'));
        $this->assertFalse($filter->contains('This is a classic example'));
    }

    public function test_clean_reply_still_posts_after_moderation_is_enabled(): void
    {
        FoulWord::query()->create(['word' => 'fuck', 'is_active' => true]);

        $user = User::factory()->create(['email_verified_at' => now()]);
        $topic = DiscussionTopic::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->postJson(route('discussions.replies.store', $topic), [
                'body' => 'Try cover cropping between seasons.',
            ])
            ->assertOk();

        $this->assertDatabaseHas('discussion_replies', [
            'discussion_topic_id' => $topic->id,
            'body' => 'Try cover cropping between seasons.',
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}
