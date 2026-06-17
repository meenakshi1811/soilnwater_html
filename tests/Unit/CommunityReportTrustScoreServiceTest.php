<?php

namespace Tests\Unit;

use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\CommunityPostReaction;
use App\Models\CommunityReportSupport;
use App\Models\CommunityPostSave;
use App\Models\User;
use App\Services\CommunityReportTrustScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityReportTrustScoreServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_trust_score_reaches_one_hundred_when_all_factors_are_met(): void
    {
        $author = User::factory()->create();
        $admin = User::factory()->create(['role' => 'admin']);
        $supporter = User::factory()->create();
        $secondSupporter = User::factory()->create();

        $post = CommunityPost::query()->create([
            'user_id' => $author->id,
            'content_type' => 'reports',
            'category' => 'Water Report',
            'title' => 'Verified Water Report',
            'slug' => 'verified-water-report',
            'body' => 'Detailed report body with enough context for readers.',
            'featured_images' => ['uploads/community-posts/sample.jpg'],
            'featured_image_path' => 'uploads/community-posts/sample.jpg',
            'location_type' => CommunityPost::LOCATION_TYPE_GPS,
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => now(),
            'reviewed_at' => now(),
            'reviewed_by' => $admin->id,
            'meta' => [
                'report_status' => 'Information Only',
                'report_type' => 'Field Report',
                'issue_attachments' => [
                    ['name' => 'survey.pdf', 'type' => 'application', 'url' => '/uploads/community-posts/issues/survey.pdf', 'path' => 'uploads/community-posts/issues/survey.pdf'],
                    ['name' => 'site-photo.jpg', 'type' => 'image', 'url' => '/uploads/community-posts/issues/site-photo.jpg', 'path' => 'uploads/community-posts/issues/site-photo.jpg'],
                ],
            ],
        ]);

        foreach (['Support', 'Vote', 'Helpful'] as $reaction) {
            CommunityPostReaction::query()->create([
                'community_post_id' => $post->id,
                'user_id' => $supporter->id,
                'reaction' => $reaction,
            ]);
        }

        CommunityPostSave::query()->create([
            'community_post_id' => $post->id,
            'user_id' => $supporter->id,
        ]);

        CommunityPostSave::query()->create([
            'community_post_id' => $post->id,
            'user_id' => $secondSupporter->id,
        ]);

        CommunityReportSupport::query()->create([
            'community_post_id' => $post->id,
            'user_id' => $supporter->id,
        ]);

        CommunityPostComment::query()->create([
            'community_post_id' => $post->id,
            'user_id' => $supporter->id,
            'body' => 'This report looks credible and well documented.',
        ]);

        $breakdown = CommunityReportTrustScoreService::breakdown($post->fresh());

        $this->assertTrue($breakdown['evidence_provided']['met']);
        $this->assertTrue($breakdown['location_verified']['met']);
        $this->assertTrue($breakdown['documents_attached']['met']);
        $this->assertTrue($breakdown['community_support']['met']);
        $this->assertTrue($breakdown['admin_verification']['met']);
        $this->assertSame(100, CommunityReportTrustScoreService::score($post->fresh()));
    }

    public function test_report_trust_score_starts_low_for_minimal_draft_reports(): void
    {
        $author = User::factory()->create();

        $post = CommunityPost::query()->create([
            'user_id' => $author->id,
            'content_type' => 'reports',
            'category' => 'Water Report',
            'title' => 'Minimal Draft Report',
            'slug' => 'minimal-draft-report',
            'body' => 'Draft report without supporting evidence yet.',
            'status' => CommunityPost::STATUS_DRAFT,
            'meta' => [
                'report_status' => 'Information Only',
                'report_type' => 'Observation Report',
            ],
        ]);

        $this->assertSame(0, CommunityReportTrustScoreService::score($post));
    }
}
