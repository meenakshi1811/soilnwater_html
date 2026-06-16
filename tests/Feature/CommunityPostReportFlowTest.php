<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\CommunityPostComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CommunityPostReportFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_posts_require_my_area_fields(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'reports',
            'category' => 'Community Problem Report',
            'title' => 'Quarterly Water Market Report',
            'excerpt' => 'A professional overview of water market performance.',
            'body' => 'This report contains detailed analysis for the water market this quarter.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'location_type' => 'city',
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'report_type',
            'issue_priority',
        ]);
    }

    public function test_report_posts_store_my_area_and_optional_report_metadata(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'reports',
            'category' => 'Community Problem Report',
            'title' => 'District Water Conservation Report',
            'excerpt' => 'Executive summary covering objective, findings, and action points.',
            'body' => 'Background, context, analysis, evidence, limitations, conclusion, and appendix notes are included here.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'allow_comments' => '1',
            'location_type' => 'city',
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
            'report_type' => 'Water Issue',
            'issue_priority' => 'High',
            'issue_status' => 'Open',
            'reported_to' => 'Water Department',
            'report_subtitle' => 'Water availability and local action priorities',
            'reporting_period' => 'Q1 2026',
            'report_date' => '2026-04-15',
            'prepared_by' => 'Soil & Water Research Desk',
            'report_scope' => 'Assess water availability and conservation opportunities.',
            'methodology' => 'Reviewed local observations, public data, and stakeholder inputs.',
            'data_sources' => 'Field survey, public datasets, and local department notes.',
            'key_findings' => "Storage capacity is uneven.\nAwareness programs need stronger follow-up.",
            'recommendations' => "Prioritize recharge projects.\nPublish monthly progress dashboards.",
        ]);

        $response->assertOk()->assertJson([
            'message' => 'Community post submitted for admin approval.',
        ]);

        $post = CommunityPost::query()->where('title', 'District Water Conservation Report')->firstOrFail();

        $this->assertSame(CommunityPost::STATUS_PENDING, $post->status);

        $this->assertTrue($post->allow_comments);
        $this->assertSame('reports', $post->content_type);
        $this->assertSame('Jaipur, Rajasthan, India', $post->location);
        $this->assertSame('26.9124000', (string) $post->location_lat);
        $this->assertSame('75.7873000', (string) $post->location_lng);
        $this->assertSame('Water Issue', $post->meta['report_type']);
        $this->assertSame('High', $post->meta['issue_priority']);
        $this->assertSame('Q1 2026', $post->meta['reporting_period']);
        $this->assertSame('2026-04-15', $post->meta['report_date']);
        $this->assertSame('Soil & Water Research Desk', $post->meta['prepared_by']);
        $this->assertSame('Field survey, public datasets, and local department notes.', $post->meta['data_sources']);
        $this->assertSame("Prioritize recharge projects.\nPublish monthly progress dashboards.", $post->meta['recommendations']);
    }

    public function test_news_posts_require_professional_news_fields(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'news',
            'category' => 'Local News',
            'title' => 'Canal Repair Work Begins',
            'excerpt' => 'A concise newsroom summary for the local update.',
            'body' => 'This news story contains enough details about the local canal repair work.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'location_type' => 'city',
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'news_dateline',
            'news_date',
            'reporter_name',
            'news_source',
            'fact_summary',
            'verification_notes',
        ]);
    }

    public function test_news_posts_store_professional_news_metadata(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'news',
            'category' => 'Local News',
            'title' => 'Water Supply Schedule Updated',
            'excerpt' => 'Residents will receive updated water supply timings from Monday.',
            'body' => 'The municipal office announced updated water supply timings after reviewing summer demand.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'allow_comments' => '1',
            'location_type' => 'city',
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
            'news_subtitle' => 'Municipal update for summer demand',
            'news_dateline' => 'Jaipur',
            'news_date' => '2026-06-12T10:30',
            'reporter_name' => 'Community News Desk',
            'news_source' => 'Municipal water department release',
            'source_url' => 'https://example.com/water-schedule',
            'fact_summary' => 'The schedule changes start Monday and apply to three wards.',
            'verification_notes' => 'Confirmed against the municipal release and ward notice.',
            'impact_area' => 'Residents in wards 10, 11, and 12.',
            'quote_attribution' => 'Department spokesperson said supply pressure will be monitored.',
        ]);

        $response->assertOk()->assertJson([
            'message' => 'Community post submitted for admin approval.',
        ]);

        $post = CommunityPost::query()->where('title', 'Water Supply Schedule Updated')->firstOrFail();

        $this->assertSame(CommunityPost::STATUS_PENDING, $post->status);

        $this->assertTrue($post->allow_comments);
        $this->assertSame('news', $post->content_type);
        $this->assertSame('Jaipur', $post->meta['news_dateline']);
        $this->assertSame('2026-06-12T10:30', $post->meta['news_date']);
        $this->assertSame('Community News Desk', $post->meta['reporter_name']);
        $this->assertSame('Municipal water department release', $post->meta['news_source']);
        $this->assertSame('https://example.com/water-schedule', $post->meta['source_url']);
    }

    public function test_report_posts_can_store_my_area_issue_details_and_attachments(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'reports',
            'category' => 'Community Problem Report',
            'report_type' => 'Water Issue',
            'title' => 'Broken water pipeline near market',
            'excerpt' => 'Water has been leaking near the market and affecting nearby shops.',
            'body' => 'The water pipeline has been broken for three days and needs urgent repair from the local department.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'allow_comments' => '1',
            'location_type' => 'city',
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
            'issue_priority' => 'Urgent',
            'issue_status' => 'Open',
            'reported_to' => 'Water Department',
            'issue_reference' => 'WD-123',
            'issue_attachments' => [
                UploadedFile::fake()->image('leak.jpg'),
                UploadedFile::fake()->create('complaint.pdf', 64, 'application/pdf'),
            ],
        ]);

        $response->assertOk()->assertJson([
            'message' => 'Community post submitted for admin approval.',
        ]);

        $post = CommunityPost::query()->where('title', 'Broken water pipeline near market')->firstOrFail();

        $this->assertSame(CommunityPost::STATUS_PENDING, $post->status);

        $this->assertTrue($post->allow_comments);
        $this->assertSame('reports', $post->content_type);
        $this->assertSame('Water Issue', $post->meta['report_type']);
        $this->assertSame('Urgent', $post->meta['issue_priority']);
        $this->assertSame('Open', $post->meta['issue_status']);
        $this->assertSame('Water Department', $post->meta['reported_to']);
        $this->assertCount(2, $post->meta['issue_attachments']);
    }

    public function test_my_voice_post_type_is_not_accepted_from_the_form_flow(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'my-voice',
            'category' => 'Personal Opinion',
            'title' => 'Why local water awareness matters',
            'excerpt' => 'A personal experience about water conservation awareness.',
            'body' => 'This opinion explains why local water awareness programs need regular community participation.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'location_type' => 'city',
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
        ])->assertUnprocessable()->assertJsonValidationErrors(['content_type']);
    }

    public function test_enabled_posts_accept_threaded_discussion_comments(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);
        $reader = User::factory()->create(['email_verified_at' => now()]);

        $post = CommunityPost::query()->create([
            'user_id' => $author->id,
            'content_type' => 'news',
            'category' => 'Local News',
            'title' => 'Readers can discuss this post',
            'slug' => 'readers-can-discuss-this-post',
            'body' => 'This news post contains enough public information for a discussion thread.',
            'meta' => ['location' => 'Jaipur'],
            'allow_comments' => true,
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->actingAs($reader)->post(route('community.comments.store', $post), [
            'body' => 'Can someone explain how this update affects nearby wards?',
        ])->assertRedirect();

        $comment = CommunityPostComment::query()->where('community_post_id', $post->id)->firstOrFail();

        $this->assertNull($comment->parent_id);
        $this->assertSame($reader->id, $comment->user_id);
        $this->assertSame('Can someone explain how this update affects nearby wards?', $comment->body);

        $this->actingAs($author)->post(route('community.comments.store', $post), [
            'parent_id' => $comment->id,
            'body' => 'Yes, the change applies to wards listed in the public notice.',
        ])->assertRedirect();

        $this->assertDatabaseHas('community_post_comments', [
            'community_post_id' => $post->id,
            'parent_id' => $comment->id,
            'body' => 'Yes, the change applies to wards listed in the public notice.',
        ]);
    }

    public function test_disabled_posts_reject_discussion_comments(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);
        $reader = User::factory()->create(['email_verified_at' => now()]);

        $post = CommunityPost::query()->create([
            'user_id' => $author->id,
            'content_type' => 'news',
            'category' => 'Local News',
            'title' => 'Closed discussion post',
            'slug' => 'closed-discussion-post',
            'body' => 'This news post has comments disabled by the author.',
            'meta' => ['location' => 'Jaipur'],
            'allow_comments' => false,
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->actingAs($reader)->post(route('community.comments.store', $post), [
            'body' => 'This should not be stored.',
        ])->assertForbidden();

        $this->assertDatabaseMissing('community_post_comments', [
            'community_post_id' => $post->id,
            'body' => 'This should not be stored.',
        ]);
    }
}
