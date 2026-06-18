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

    /**
     * @return array<string, string>
     */
    private function structuredLocationPayload(): array
    {
        return [
            'location_country' => 'India',
            'location_state' => 'Rajasthan',
            'location_district' => 'Jaipur',
            'location_city' => 'Jaipur',
            'location_locality' => 'Malviya Nagar',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
        ];
    }

    public function test_report_posts_require_my_area_fields(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'reports',
            'category' => 'Water Report',
            'writing_purpose' => 'Raise Awareness',
            'title' => 'Quarterly Water Market Report',
            'excerpt' => 'A professional overview of water market performance.',
            'body' => 'This report contains detailed analysis for the water market this quarter.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            ...$this->structuredLocationPayload(),
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'report_status',
            'report_type',
            'issue_priority',
            'report_author_type',
        ]);
    }

    public function test_report_posts_store_my_area_and_optional_report_metadata(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'reports',
            'category' => 'Water Report',
            'writing_purpose' => 'Raise Awareness',
            'title' => 'District Water Conservation Report',
            'excerpt' => 'Executive summary covering objective, findings, and action points.',
            'body' => 'Background, context, analysis, evidence, limitations, conclusion, and appendix notes are included here.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'publish_as' => CommunityPost::PUBLISH_AS_PUBLIC_PROFILE,
            'allow_comments' => '1',
            ...$this->structuredLocationPayload(),
            'report_status' => 'Awareness Campaign',
            'report_type' => 'Research Report',
            'issue_priority' => 'High',
            'report_author_type' => 'Citizen Reporter',
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
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
        ]);

        $response->assertOk()->assertJson([
            'message' => 'Community post submitted for admin approval.',
        ]);

        $post = CommunityPost::query()->where('title', 'District Water Conservation Report')->firstOrFail();

        $this->assertSame(CommunityPost::STATUS_PENDING, $post->status);

        $this->assertTrue($post->allow_comments);
        $this->assertSame('reports', $post->content_type);
        $this->assertSame('India', $post->meta['location_country']);
        $this->assertSame('Rajasthan', $post->meta['location_state']);
        $this->assertSame('Jaipur', $post->meta['location_city']);
        $this->assertSame('Malviya Nagar, Jaipur, Rajasthan, India', $post->location);
        $this->assertSame('26.9124000', (string) $post->location_lat);
        $this->assertSame('75.7873000', (string) $post->location_lng);
        $this->assertSame('Awareness Campaign', $post->meta['report_status']);
        $this->assertSame('Research Report', $post->meta['report_type']);
        $this->assertSame('High', $post->meta['issue_priority']);
        $this->assertSame('Q1 2026', $post->meta['reporting_period']);
        $this->assertSame('2026-04-15', $post->meta['report_date']);
        $this->assertSame('Soil & Water Research Desk', $post->meta['prepared_by']);
        $this->assertSame('Field survey, public datasets, and local department notes.', $post->meta['data_sources']);
        $this->assertSame("Prioritize recharge projects.\nPublish monthly progress dashboards.", $post->meta['recommendations']);
    }

    /**
     * @return array<string, string>
     */
    private function newsContentPayload(): array
    {
        return [
            'news_what_happened' => 'The municipal office announced updated water supply timings.',
            'news_where_happened' => 'Jaipur, Rajasthan',
            'news_when_happened' => 'Effective from Monday morning',
            'news_who_involved' => 'Municipal water department and ward residents',
            'news_why_important' => 'Summer demand requires adjusted supply windows.',
            'news_current_status' => 'Schedule published and active from Monday.',
        ];
    }

    public function test_news_posts_require_professional_news_fields(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'news',
            'category' => 'Local News',
            'writing_purpose' => 'Share Knowledge',
            'title' => 'Canal Repair Work Begins',
            'excerpt' => 'A concise newsroom summary for the local update.',
            'body' => 'This news story contains enough details about the local canal repair work.',
            'status' => CommunityPost::STATUS_DRAFT,
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'news_type',
            'event_date',
            'location_country',
            'location_state',
            'location_district',
            'location_city',
            'news_dateline',
            'news_date',
            'reporter_name',
            'news_source_type',
            'news_source',
            'news_what_happened',
            'news_where_happened',
            'news_when_happened',
            'news_who_involved',
            'news_why_important',
            'news_current_status',
            'verification_notes',
            'news_impact_level',
            'news_affected_group',
        ]);
    }

    public function test_news_posts_store_professional_news_metadata(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'news',
            'category' => 'Local News',
            'writing_purpose' => 'Share Knowledge',
            'title' => 'Water Supply Schedule Updated',
            'excerpt' => 'Residents will receive updated water supply timings from Monday.',
            'body' => 'The municipal office announced updated water supply timings after reviewing summer demand.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'publish_as' => CommunityPost::PUBLISH_AS_PUBLIC_PROFILE,
            'allow_comments' => '1',
            'allow_questions' => '1',
            'allow_suggestions' => '1',
            ...$this->structuredLocationPayload(),
            'news_type' => 'General News',
            'event_date' => '2026-06-15',
            'event_time' => '7:30 PM',
            'news_subtitle' => 'Municipal update for summer demand',
            'news_dateline' => 'Jaipur',
            'news_date' => '2026-06-12T10:30',
            'reporter_name' => 'Community News Desk',
            'news_source_type' => 'Official Source',
            'news_source' => 'Municipal water department release',
            'source_url' => 'https://example.com/water-schedule',
            ...$this->newsContentPayload(),
            'verification_notes' => 'Confirmed against the municipal release and ward notice.',
            'news_impact_level' => 'Medium',
            'news_affected_group' => 'Residents',
            'news_priority' => 'Important',
            'news_people_organizations' => 'Mayor, Municipal water department',
            'impact_area' => 'Residents in wards 10, 11, and 12.',
            'quote_attribution' => 'Department spokesperson said supply pressure will be monitored.',
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
        ]);

        $response->assertOk()->assertJson([
            'message' => 'Community post submitted for admin approval.',
        ]);

        $post = CommunityPost::query()->where('title', 'Water Supply Schedule Updated')->firstOrFail();

        $this->assertSame(CommunityPost::STATUS_PENDING, $post->status);

        $this->assertTrue($post->allow_comments);
        $this->assertTrue($post->allow_questions);
        $this->assertTrue($post->allow_suggestions);
        $this->assertSame('news', $post->content_type);
        $this->assertSame('General News', $post->meta['news_type']);
        $this->assertSame('2026-06-15', $post->meta['event_date']);
        $this->assertSame('7:30 PM', $post->meta['event_time']);
        $this->assertSame('India', $post->meta['location_country']);
        $this->assertSame('Rajasthan', $post->meta['location_state']);
        $this->assertSame('Jaipur', $post->meta['news_dateline']);
        $this->assertSame('2026-06-12T10:30', $post->meta['news_date']);
        $this->assertSame('Community News Desk', $post->meta['reporter_name']);
        $this->assertSame('Official Source', $post->meta['news_source_type']);
        $this->assertSame('Municipal water department release', $post->meta['news_source']);
        $this->assertSame('The municipal office announced updated water supply timings.', $post->meta['news_what_happened']);
        $this->assertSame('https://example.com/water-schedule', $post->meta['source_url']);
        $this->assertSame('Medium', $post->meta['news_impact_level']);
        $this->assertSame('Residents', $post->meta['news_affected_group']);
        $this->assertSame('Important', $post->meta['news_priority']);
        $this->assertSame('Mayor, Municipal water department', $post->meta['news_people_organizations']);
    }

    public function test_report_posts_can_store_my_area_issue_details_and_attachments(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'reports',
            'category' => 'Water Report',
            'report_status' => 'Awareness Campaign',
            'report_type' => 'Research Report',
            'writing_purpose' => 'Help Community',
            'title' => 'Broken water pipeline near market',
            'excerpt' => 'Water has been leaking near the market and affecting nearby shops.',
            'body' => 'The water pipeline has been broken for three days and needs urgent repair from the local department.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'publish_as' => CommunityPost::PUBLISH_AS_PUBLIC_PROFILE,
            'allow_comments' => '1',
            ...$this->structuredLocationPayload(),
            'issue_priority' => 'Urgent',
            'report_author_type' => 'Citizen Reporter',
            'issue_status' => 'Open',
            'reported_to' => 'Water Department',
            'issue_reference' => 'WD-123',
            'issue_attachments' => [
                UploadedFile::fake()->image('leak.jpg'),
                UploadedFile::fake()->create('complaint.pdf', 64, 'application/pdf'),
            ],
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
        ]);

        $response->assertOk()->assertJson([
            'message' => 'Community post submitted for admin approval.',
        ]);

        $post = CommunityPost::query()->where('title', 'Broken water pipeline near market')->firstOrFail();

        $this->assertSame(CommunityPost::STATUS_PENDING, $post->status);

        $this->assertTrue($post->allow_comments);
        $this->assertSame('reports', $post->content_type);
        $this->assertSame('Awareness Campaign', $post->meta['report_status']);
        $this->assertSame('Research Report', $post->meta['report_type']);
        $this->assertSame('Urgent', $post->meta['issue_priority']);
        $this->assertSame('Open', $post->meta['issue_status']);
        $this->assertSame('Water Department', $post->meta['reported_to']);
        $this->assertCount(2, $post->meta['issue_attachments']);
        foreach ($post->meta['issue_attachments'] as $attachment) {
            $this->assertStringStartsWith('uploads/community-posts/issues/', $attachment['path']);
            $this->assertFileExists(public_path($attachment['path']));
            $this->assertStringContainsString('/uploads/community-posts/issues/', $attachment['url']);
        }
    }

    public function test_my_voice_post_type_is_not_accepted_from_the_form_flow(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'my-voice',
            'category' => 'Personal Opinion',
            'writing_purpose' => 'Personal Experience',
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

    public function test_report_posts_can_store_report_period_author_and_organization_details(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'name' => 'Asha Verma',
        ]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'reports',
            'category' => 'Research Report',
            'report_status' => 'Awareness Campaign',
            'report_type' => 'Research Report',
            'issue_priority' => 'Medium',
            'report_author_type' => 'Researcher',
            'observation_period_from' => '2025-01-01',
            'observation_period_to' => '2025-12-31',
            'key_findings' => "Soil organic matter declined in sampled plots.\nIrrigation coverage remains uneven.",
            'report_analysis' => 'The decline correlates with reduced compost use and irregular water supply.',
            'recommendations' => "Expand drip irrigation pilots.\nRun quarterly soil testing camps.",
            'report_conclusion' => 'Targeted recharge and soil health programs can improve outcomes within one season.',
            'action_needed' => 'Yes',
            'action_requested_from' => 'Municipality',
            'suggested_solution' => 'Install community rainwater harvesting units and publish a monthly maintenance schedule.',
            'organization_type' => 'Institution',
            'organization_name' => 'Soil & Water Research Institute',
            'writing_purpose' => 'Research Findings',
            'title' => 'Annual Soil Health Report',
            'body' => 'Detailed annual soil health findings and recommendations.',
            'status' => CommunityPost::STATUS_DRAFT,
            ...$this->structuredLocationPayload(),
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
        ]);

        $response->assertOk();

        $post = CommunityPost::query()->where('title', 'Annual Soil Health Report')->firstOrFail();

        $this->assertSame('India', $post->meta['location_country']);
        $this->assertSame('2025-01-01', $post->meta['observation_period_from']);
        $this->assertSame('2025-12-31', $post->meta['observation_period_to']);
        $this->assertSame('Asha Verma', $post->meta['report_author_name']);
        $this->assertSame('Researcher', $post->meta['report_author_type']);
        $this->assertSame('Institution', $post->meta['organization_type']);
        $this->assertSame('Soil & Water Research Institute', $post->meta['organization_name']);
        $this->assertStringContainsString('Soil organic matter declined', $post->meta['key_findings']);
        $this->assertStringContainsString('decline correlates', $post->meta['report_analysis']);
        $this->assertStringContainsString('drip irrigation', $post->meta['recommendations']);
        $this->assertStringContainsString('Targeted recharge', $post->meta['report_conclusion']);
        $this->assertSame('Yes', $post->meta['action_needed']);
        $this->assertSame('Municipality', $post->meta['action_requested_from']);
        $this->assertStringContainsString('rainwater harvesting', $post->meta['suggested_solution']);
    }

    public function test_report_action_requested_from_is_required_when_action_is_needed(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'reports',
            'category' => 'Water Report',
            'report_status' => 'Request for Action',
            'report_type' => 'Community Report',
            'issue_priority' => 'High',
            'report_author_type' => 'Citizen Reporter',
            'action_needed' => 'Yes',
            'writing_purpose' => 'Help Community',
            'title' => 'Community Action Validation Test',
            'body' => 'This report requests municipal follow-up without selecting an audience.',
            'status' => CommunityPost::STATUS_DRAFT,
            ...$this->structuredLocationPayload(),
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['action_requested_from']);
    }

    public function test_report_posts_can_store_optional_gps_location(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'reports',
            'category' => 'Water Report',
            'report_status' => 'Information Only',
            'report_type' => 'Field Report',
            'issue_priority' => 'High',
            'report_author_type' => 'Researcher',
            'writing_purpose' => 'Share Knowledge',
            'title' => 'GPS Survey Report',
            'body' => 'Field observations collected with optional GPS coordinates.',
            'status' => CommunityPost::STATUS_DRAFT,
            ...$this->structuredLocationPayload(),
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
        ]);

        $response->assertOk();

        $post = CommunityPost::query()->where('title', 'GPS Survey Report')->firstOrFail();

        $this->assertSame(CommunityPost::LOCATION_TYPE_VILLAGE, $post->location_type);
        $this->assertSame('26.9124000', (string) $post->location_lat);
        $this->assertSame('75.7873000', (string) $post->location_lng);
        $this->assertSame('Malviya Nagar', $post->meta['location_locality']);
    }

    public function test_gps_location_type_is_rejected_for_non_report_posts(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'articles',
            'category' => 'Education',
            'writing_purpose' => 'Share Knowledge',
            'title' => 'Article With GPS',
            'body' => 'This article should not accept GPS-only location type.',
            'status' => CommunityPost::STATUS_DRAFT,
            'location_type' => CommunityPost::LOCATION_TYPE_GPS,
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['location_type']);
    }
}
