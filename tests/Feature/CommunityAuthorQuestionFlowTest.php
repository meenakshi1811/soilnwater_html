<?php

namespace Tests\Feature;

use App\Mail\CommunityAuthorQuestionAnsweredMail;
use App\Mail\CommunityAuthorQuestionReceivedMail;
use App\Models\CommunityAuthorQuestion;
use App\Models\CommunityPost;
use App\Models\User;
use App\Notifications\PortalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CommunityAuthorQuestionFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_ask_author_question_on_post(): void
    {
        $author = User::factory()->create();
        $post = CommunityPost::factory()->create([
            'user_id' => $author->id,
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $response = $this->post(route('community.author-questions.store.post', $post), [
            'question' => 'What inspired you to write this post?',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('community_author_questions', 0);
    }

    public function test_logged_in_reader_can_ask_question_on_post_and_notify_author(): void
    {
        Mail::fake();
        Notification::fake();

        $author = User::factory()->create([
            'email_verified_at' => now(),
            'email' => 'author@example.com',
        ]);
        $reader = User::factory()->create(['email_verified_at' => now()]);
        $post = CommunityPost::factory()->create([
            'user_id' => $author->id,
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($reader)->postJson(route('community.author-questions.store.post', $post), [
            'question' => 'What inspired you to write this post?',
        ]);

        $response->assertOk()->assertJsonFragment([
            'message' => 'Your question has been sent to the author. You will be notified when it is answered.',
        ]);

        $question = CommunityAuthorQuestion::query()->firstOrFail();
        $this->assertSame($author->id, $question->author_id);
        $this->assertSame($reader->id, $question->asked_by);
        $this->assertSame($post->id, $question->community_post_id);
        $this->assertNull($question->answer);

        Notification::assertSentTo($author, PortalNotification::class, function (PortalNotification $notification): bool {
            $data = $notification->toArray(new User());

            return str_contains($data['title'], 'New question from a reader');
        });

        Mail::assertSent(CommunityAuthorQuestionReceivedMail::class);
    }

    public function test_author_can_answer_question_and_notify_reader(): void
    {
        Mail::fake();
        Notification::fake();

        $author = User::factory()->create([
            'email_verified_at' => now(),
            'email' => 'author@example.com',
        ]);
        $reader = User::factory()->create([
            'email_verified_at' => now(),
            'email' => 'reader@example.com',
        ]);
        $post = CommunityPost::factory()->create([
            'user_id' => $author->id,
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $question = CommunityAuthorQuestion::query()->create([
            'author_id' => $author->id,
            'asked_by' => $reader->id,
            'community_post_id' => $post->id,
            'question' => 'What inspired you to write this post?',
        ]);

        $response = $this->actingAs($author)->postJson(route('community.author-questions.answer', $question), [
            'answer' => 'I was inspired by local water conservation efforts in my village.',
        ]);

        $response->assertOk()->assertJson([
            'message' => 'Your answer has been published for the reader.',
        ]);

        $question->refresh();
        $this->assertSame('I was inspired by local water conservation efforts in my village.', $question->answer);
        $this->assertNotNull($question->answered_at);

        Notification::assertSentTo($reader, PortalNotification::class, function (PortalNotification $notification): bool {
            $data = $notification->toArray(new User());

            return str_contains($data['title'], 'Your question was answered');
        });

        Mail::assertSent(CommunityAuthorQuestionAnsweredMail::class);
    }

    public function test_answered_question_appears_on_post_detail_page(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);
        $reader = User::factory()->create(['email_verified_at' => now(), 'name' => 'Reader One']);
        $post = CommunityPost::factory()->create([
            'user_id' => $author->id,
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        CommunityAuthorQuestion::query()->create([
            'author_id' => $author->id,
            'asked_by' => $reader->id,
            'community_post_id' => $post->id,
            'question' => 'How do you manage soil moisture?',
            'answer' => 'Mulching and drip irrigation work best for me.',
            'answered_at' => now(),
        ]);

        $response = $this->get(route('community.show', $post));

        $response->assertOk();
        $response->assertSee('Ask Question to Author');
        $response->assertSee('How do you manage soil moisture?');
        $response->assertSee('Mulching and drip irrigation work best for me.');
        $response->assertSee('Reader One');
    }

    public function test_author_portal_lists_pending_questions(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);
        $reader = User::factory()->create(['email_verified_at' => now(), 'name' => 'Curious Reader']);

        CommunityAuthorQuestion::query()->create([
            'author_id' => $author->id,
            'asked_by' => $reader->id,
            'question' => 'Can you share more details about your method?',
        ]);

        $response = $this->actingAs($author)->get(route('community.author-questions.index'));

        $response->assertOk();
        $response->assertSee('Reader Questions');
        $response->assertSee('Can you share more details about your method?');
        $response->assertSee('Curious Reader');
        $response->assertSee('Publish answer');
    }

    public function test_reader_cannot_ask_question_to_themselves(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($author)->postJson(route('community.author-questions.store.author', $author), [
            'question' => 'Why am I asking myself a question?',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('community_author_questions', 0);
    }
}
