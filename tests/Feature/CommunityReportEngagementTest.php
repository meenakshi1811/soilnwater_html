<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\User;
use App\Notifications\PortalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommunityReportEngagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_member_can_support_agree_and_follow_a_report(): void
    {
        Notification::fake();

        $author = User::factory()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now(), 'name' => 'Community Member']);

        $post = CommunityPost::query()->create([
            'user_id' => $author->id,
            'content_type' => 'reports',
            'category' => 'Water Report',
            'title' => 'Neighborhood Drainage Issue',
            'slug' => 'neighborhood-drainage-issue',
            'body' => 'Standing water has remained for two weeks near the school crossing.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => now(),
            'meta' => [
                'report_status' => 'Request for Action',
                'report_type' => 'Community Report',
            ],
        ]);

        $this->actingAs($member)->postJson(route('community.report-engagement.support', $post))
            ->assertOk()
            ->assertJsonPath('supported', true);

        $this->actingAs($member)->postJson(route('community.report-engagement.agree', $post))
            ->assertOk()
            ->assertJsonPath('agreed', true);

        $this->actingAs($member)->postJson(route('community.report-engagement.follow', $post))
            ->assertOk()
            ->assertJsonPath('following', true);

        $this->assertDatabaseHas('community_report_supports', [
            'community_post_id' => $post->id,
            'user_id' => $member->id,
        ]);
        $this->assertDatabaseHas('community_report_agreements', [
            'community_post_id' => $post->id,
            'user_id' => $member->id,
        ]);
        $this->assertDatabaseHas('community_report_follows', [
            'community_post_id' => $post->id,
            'user_id' => $member->id,
        ]);

        Notification::assertSentTo($author, PortalNotification::class, 3);
    }

    public function test_author_cannot_use_community_reporting_actions_on_own_post(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);

        $post = CommunityPost::query()->create([
            'user_id' => $author->id,
            'content_type' => 'reports',
            'category' => 'Water Report',
            'title' => 'Own Report',
            'slug' => 'own-report',
            'body' => 'Author report body.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => now(),
            'meta' => ['report_type' => 'Field Report'],
        ]);

        $this->actingAs($author)->postJson(route('community.report-engagement.support', $post))
            ->assertStatus(422);
    }

    public function test_community_member_can_upload_report_evidence_and_notify_author(): void
    {
        Notification::fake();
        Storage::fake('local');

        $author = User::factory()->create(['email_verified_at' => now()]);
        $member = User::factory()->create(['email_verified_at' => now(), 'name' => 'Evidence Contributor']);

        $post = CommunityPost::query()->create([
            'user_id' => $author->id,
            'content_type' => 'reports',
            'category' => 'Environment Report',
            'title' => 'Illegal Dumping Report',
            'slug' => 'illegal-dumping-report',
            'body' => 'Waste has been dumped beside the canal path.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => now(),
            'allow_additional_evidence' => true,
            'meta' => ['report_type' => 'Observation Report'],
        ]);

        $file = UploadedFile::fake()->create('dump-site.pdf', 120, 'application/pdf');

        $this->actingAs($member)->postJson(route('community.participation.evidence', $post), [
            'note' => 'Photo taken this morning near the canal gate.',
            'evidence_files' => [$file],
        ])->assertOk()
            ->assertJsonPath('evidence.0.name', 'dump-site.pdf');

        $this->assertDatabaseHas('community_report_evidence', [
            'community_post_id' => $post->id,
            'user_id' => $member->id,
            'name' => 'dump-site.pdf',
        ]);

        Notification::assertSentTo($author, PortalNotification::class, function (PortalNotification $notification): bool {
            $payload = $notification->toArray(new User());

            return str_contains($payload['title'], 'New additional evidence');
        });
    }

    public function test_published_report_detail_page_shows_community_reporting_panel(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);

        $post = CommunityPost::query()->create([
            'user_id' => $author->id,
            'content_type' => 'reports',
            'category' => 'Water Report',
            'title' => 'Public Report Panel Test',
            'slug' => 'public-report-panel-test',
            'body' => 'Visible report content for the community panel.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => now(),
            'allow_comments' => true,
            'meta' => [
                'report_status' => 'Seeking Support',
                'report_type' => 'Community Report',
            ],
        ]);

        $this->get(route('community.show', $post))
            ->assertOk()
            ->assertSee('Community Reporting Features')
            ->assertSee('Support This Report')
            ->assertSee('I Agree')
            ->assertSee('Follow Issue')
            ->assertSee('Public Participation')
            ->assertDontSee('Community poll');
    }
}
