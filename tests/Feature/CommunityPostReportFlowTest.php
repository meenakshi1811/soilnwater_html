<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityPostReportFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_posts_require_professional_report_fields(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'reports',
            'category' => 'Industry Reports',
            'title' => 'Quarterly Water Market Report',
            'excerpt' => 'A professional overview of water market performance.',
            'body' => 'This report contains detailed analysis for the water market this quarter.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'reporting_period',
            'report_date',
            'prepared_by',
            'methodology',
            'data_sources',
            'key_findings',
            'recommendations',
        ]);
    }

    public function test_report_posts_store_professional_metadata_and_disable_comments(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'reports',
            'category' => 'Water & Environment Reports',
            'title' => 'District Water Conservation Report',
            'excerpt' => 'Executive summary covering objective, findings, and action points.',
            'body' => 'Background, context, analysis, evidence, limitations, conclusion, and appendix notes are included here.',
            'status' => CommunityPost::STATUS_PUBLISHED,
            'allow_comments' => '1',
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
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
            'message' => 'Community post created successfully.',
        ]);

        $post = CommunityPost::query()->where('title', 'District Water Conservation Report')->firstOrFail();

        $this->assertFalse($post->allow_comments);
        $this->assertSame('reports', $post->content_type);
        $this->assertSame('Q1 2026', $post->meta['reporting_period']);
        $this->assertSame('2026-04-15', $post->meta['report_date']);
        $this->assertSame('Soil & Water Research Desk', $post->meta['prepared_by']);
        $this->assertSame('Field survey, public datasets, and local department notes.', $post->meta['data_sources']);
        $this->assertSame("Prioritize recharge projects.\nPublish monthly progress dashboards.", $post->meta['recommendations']);
    }
}
