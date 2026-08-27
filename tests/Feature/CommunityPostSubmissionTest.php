<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\CommunityPostAuditLog;
use App\Models\FoulWord;
use App\Models\User;
use App\Services\FoulWordFilter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityPostSubmissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        FoulWordFilter::forgetCache();
    }

    public function test_post_submission_allows_draft_without_policy_checkboxes(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->postJson(route('community.posts.store'), [
                'content_type' => 'articles',
                'title' => 'Draft write-up in progress',
                'body' => 'Saving this article to finish later.',
                'status' => CommunityPost::STATUS_DRAFT,
            ])
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Post saved successfully. You can publish it later from My Posts.',
            ]);

        $this->assertDatabaseHas('community_posts', [
            'user_id' => $user->id,
            'title' => 'Draft write-up in progress',
            'status' => CommunityPost::STATUS_DRAFT,
        ]);
    }

    public function test_post_submission_requires_policy_checkboxes_for_publish(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->postJson(route('community.posts.store'), [
                'content_type' => 'articles',
                'category' => 'Education',
                'writing_purpose' => 'Share Knowledge',
                'title' => 'Missing Policy Acceptance',
                'body' => 'This article body contains enough content to pass validation for publishing.',
                'status' => CommunityPost::STATUS_PUBLISHED,
                'location_type' => 'city',
                'location' => 'Jaipur, Rajasthan, India',
                'location_lat' => '26.9124000',
                'location_lng' => '75.7873000',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'accept_content_responsibility',
                'accept_original_work_indemnity',
            ]);
    }

    public function test_post_submission_stores_ip_account_and_acceptance_timestamps(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->postJson(route('community.posts.store'), $this->validPayload())
            ->assertOk();

        $post = CommunityPost::query()->where('title', 'Policy Tracking Test Post')->firstOrFail();

        $this->assertSame($user->id, $post->user_id);
        $this->assertNotNull($post->submission_ip);
        $this->assertNotNull($post->content_responsibility_accepted_at);
        $this->assertNotNull($post->original_work_accepted_at);
        $this->assertDatabaseHas('community_post_audit_logs', [
            'community_post_id' => $post->id,
            'user_id' => $user->id,
            'action' => 'created',
        ]);
    }

    public function test_post_update_creates_audit_log_entry(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->postJson(route('community.posts.store'), $this->validPayload())->assertOk();

        $post = CommunityPost::query()->where('title', 'Policy Tracking Test Post')->firstOrFail();

        $this->actingAs($user)
            ->putJson(route('community.posts.update', $post), array_merge($this->validPayload(), [
                'title' => 'Updated Policy Tracking Post',
            ]))
            ->assertOk();

        $this->assertDatabaseHas('community_post_audit_logs', [
            'community_post_id' => $post->id,
            'user_id' => $user->id,
            'action' => 'updated',
        ]);

        $this->assertGreaterThan(0, CommunityPostAuditLog::query()->where('community_post_id', $post->id)->where('action', 'updated')->count());
    }

    public function test_community_posting_policy_page_renders(): void
    {
        $this->get(route('frontend.community-posting-policy'))
            ->assertOk()
            ->assertSee('SOILNWATER COMMUNITY POSTING POLICY')
            ->assertSee('PURPOSE OF MY VOICE');
    }

    public function test_frontend_layout_shows_content_responsibility_disclaimer_on_community_pages(): void
    {
        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee('Content Responsibility Disclaimer')
            ->assertSee('SoilnWater provides a technology platform');

        $this->get(route('frontend.index'))
            ->assertOk()
            ->assertDontSee('Content Responsibility Disclaimer');
    }

    public function test_article_submission_ignores_empty_childrens_corner_quiz_fields(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->postJson(route('community.posts.store'), array_merge($this->validPayload(), [
                'childrens_corner_quiz' => [
                    [
                        'question' => '',
                        'options' => ['', '', ''],
                        'correct_answer' => '',
                    ],
                ],
            ]))
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Post saved successfully. You can publish it later from My Posts.',
            ]);

        $post = CommunityPost::query()->where('title', 'Policy Tracking Test Post')->firstOrFail();
        $this->assertNull(data_get($post->meta, 'childrens_corner_quiz'));
    }

    public function test_create_rejects_foul_word_in_title(): void
    {
        FoulWord::query()->create(['word' => 'fuck', 'is_active' => true]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->postJson(route('community.posts.store'), array_merge($this->validPayload(), [
                'title' => 'This fuck title should fail',
            ]))
            ->assertUnprocessable()
            ->assertJsonFragment(['You have used the foul word.']);

        $this->assertDatabaseMissing('community_posts', [
            'title' => 'This fuck title should fail',
        ]);
    }

    public function test_create_rejects_foul_word_in_html_body(): void
    {
        FoulWord::query()->create(['word' => 'shit', 'is_active' => true]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->postJson(route('community.posts.store'), array_merge($this->validPayload(), [
                'body' => '<p>This is a <strong>shit</strong> article for readers.</p>',
            ]))
            ->assertUnprocessable()
            ->assertJsonFragment(['You have used the foul word.']);
    }

    public function test_update_rejects_foul_word_in_excerpt(): void
    {
        FoulWord::query()->create(['word' => 'bitch', 'is_active' => true]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->postJson(route('community.posts.store'), $this->validPayload())->assertOk();
        $post = CommunityPost::query()->where('title', 'Policy Tracking Test Post')->firstOrFail();

        $this->actingAs($user)
            ->putJson(route('community.posts.update', $post), array_merge($this->validPayload(), [
                'excerpt' => 'Do not use this bitch word here',
            ]))
            ->assertUnprocessable()
            ->assertJsonFragment(['You have used the foul word.']);

        $this->assertNull($post->fresh()->excerpt);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'content_type' => 'articles',
            'article_type' => 'Guide',
            'category' => 'Education',
            'writing_purpose' => 'Share Knowledge',
            'title' => 'Policy Tracking Test Post',
            'body' => 'This article body contains enough content to pass validation for publishing.',
            'status' => CommunityPost::STATUS_DRAFT,
            'location_type' => 'city',
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
        ];
    }
}
