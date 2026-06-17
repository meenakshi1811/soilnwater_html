<?php

namespace Tests\Feature;

use App\Mail\CommunityPostParticipationReceivedMail;
use App\Models\CommunityPost;
use App\Models\User;
use App\Notifications\PortalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommunityPostParticipationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_submit_suggestion_and_notify_author(): void
    {
        Notification::fake();
        Mail::fake();

        $author = User::factory()->create(['email_verified_at' => now(), 'email' => 'author@example.com']);
        $member = User::factory()->create(['email_verified_at' => now(), 'name' => 'Suggestion Member']);

        $post = $this->publishedPost($author, [
            'allow_suggestions' => true,
        ]);

        $this->actingAs($member)->postJson(route('community.participation.suggestion', $post), [
            'body' => 'Install drainage covers near the school crossing.',
        ])->assertOk();

        $this->assertDatabaseHas('community_post_participations', [
            'community_post_id' => $post->id,
            'user_id' => $member->id,
            'type' => 'suggestion',
        ]);

        Notification::assertSentTo($author, PortalNotification::class, function (PortalNotification $notification): bool {
            $payload = $notification->toArray(new User());

            return str_contains($payload['title'], 'New public suggestion');
        });

        Mail::assertSent(CommunityPostParticipationReceivedMail::class, function (CommunityPostParticipationReceivedMail $mail) use ($author): bool {
            return $mail->hasTo($author->email);
        });
    }

    public function test_member_can_submit_feedback_and_notify_author(): void
    {
        Notification::fake();
        Mail::fake();

        $author = User::factory()->create(['email_verified_at' => now(), 'email' => 'author@example.com']);
        $member = User::factory()->create(['email_verified_at' => now()]);

        $post = $this->publishedPost($author, [
            'allow_feedback' => true,
        ]);

        $this->actingAs($member)->postJson(route('community.participation.feedback', $post), [
            'body' => 'Please add more detail about the affected households.',
        ])->assertOk();

        Notification::assertSentTo($author, PortalNotification::class, function (PortalNotification $notification): bool {
            $payload = $notification->toArray(new User());

            return str_contains($payload['title'], 'New public feedback');
        });

        Mail::assertSent(CommunityPostParticipationReceivedMail::class);
    }

    public function test_member_can_upload_additional_evidence_when_enabled(): void
    {
        Notification::fake();
        Storage::fake('local');

        $author = User::factory()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now()]);

        $post = $this->publishedPost($author, [
            'allow_additional_evidence' => true,
        ]);

        $file = UploadedFile::fake()->create('flood-photo.pdf', 120, 'application/pdf');

        $this->actingAs($member)->postJson(route('community.participation.evidence', $post), [
            'note' => 'Taken after the rain last night.',
            'evidence_files' => [$file],
        ])->assertOk()
            ->assertJsonPath('evidence.0.name', 'flood-photo.pdf');

        $this->assertDatabaseHas('community_report_evidence', [
            'community_post_id' => $post->id,
            'user_id' => $member->id,
        ]);

        Notification::assertSentTo($author, PortalNotification::class, function (PortalNotification $notification): bool {
            $payload = $notification->toArray(new User());

            return str_contains($payload['title'], 'New additional evidence');
        });
    }

    public function test_author_cannot_submit_public_participation_on_own_post(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);

        $post = $this->publishedPost($author, [
            'allow_suggestions' => true,
            'allow_feedback' => true,
            'allow_additional_evidence' => true,
        ]);

        $this->actingAs($author)->postJson(route('community.participation.suggestion', $post), [
            'body' => 'My own suggestion',
        ])->assertStatus(422);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function publishedPost(User $author, array $overrides = []): CommunityPost
    {
        return CommunityPost::query()->create(array_merge([
            'user_id' => $author->id,
            'content_type' => 'reports',
            'category' => 'Water Report',
            'title' => 'Participation Test Report',
            'slug' => 'participation-test-report-'.uniqid(),
            'body' => 'Report body for participation tests.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => now(),
            'meta' => ['report_type' => 'Community Report'],
        ], $overrides));
    }
}
