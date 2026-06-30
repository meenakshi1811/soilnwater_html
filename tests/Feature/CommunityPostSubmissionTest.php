<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\CommunityPostAuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityPostSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_submission_requires_policy_checkboxes(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->postJson(route('community.posts.store'), [
                'content_type' => 'articles',
                'category' => 'Education',
                'writing_purpose' => 'Share Knowledge',
                'title' => 'Missing Policy Acceptance',
                'body' => 'This article body contains enough content to pass validation for publishing.',
                'status' => CommunityPost::STATUS_DRAFT,
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
