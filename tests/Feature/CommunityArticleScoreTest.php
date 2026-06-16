<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\CommunityPostReaction;
use App\Models\User;
use App\Services\CommunityArticleScoreService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityArticleScoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_post_view_increments_views_count_once_per_session(): void
    {
        $post = CommunityPost::factory()->create();

        $this->get(route('community.show', $post))->assertOk();
        $this->get(route('community.show', $post))->assertOk();

        $post->refresh();

        $this->assertSame(1, $post->views_count);
    }

    public function test_share_tracking_increments_shares_count(): void
    {
        $post = CommunityPost::factory()->create();

        $this->postJson(route('community.share.track', $post))
            ->assertOk()
            ->assertJson(['shares_count' => 1]);
    }

    public function test_admin_can_set_quality_score_and_recalculate_article_score(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $post = CommunityPost::factory()->create([
            'views_count' => 120,
            'shares_count' => 10,
            'meta' => ['reading_time' => '6'],
        ]);

        CommunityPostReaction::query()->create([
            'community_post_id' => $post->id,
            'user_id' => User::factory()->create()->id,
            'reaction' => 'Helpful',
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.community-posts.quality-score', $post), [
                'quality_score' => 85,
            ])
            ->assertOk();

        $post->refresh();

        $this->assertSame('85.00', (string) $post->quality_score);
        $this->assertGreaterThan(0, (float) $post->article_score);
    }

    public function test_recalculate_can_auto_assign_article_badges(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $post = CommunityPost::factory()->create([
            'views_count' => 250,
            'shares_count' => 15,
            'quality_score' => 80,
            'published_at' => now()->subDays(2),
            'meta' => ['reading_time' => '7'],
        ]);

        for ($i = 0; $i < 8; $i++) {
            CommunityPostReaction::query()->create([
                'community_post_id' => $post->id,
                'user_id' => User::factory()->create()->id,
                'reaction' => 'Helpful',
            ]);
        }

        \App\Models\CommunityPostComment::query()->create([
            'community_post_id' => $post->id,
            'user_id' => User::factory()->create()->id,
            'body' => 'Great article with useful insights.',
        ]);

        $this->actingAs($admin)
            ->postJson(route('admin.community-posts.recalculate-score', $post), [
                'auto_assign_badges' => 1,
            ])
            ->assertOk();

        $post->refresh();

        $this->assertTrue($post->badge_trending);
        $this->assertTrue($post->badge_editors_choice);
        $this->assertContains('Trending', $post->articleScoreBadgeLabels());
    }

    public function test_article_score_badges_render_on_community_hub_cards(): void
    {
        $post = CommunityPost::factory()->create([
            'badge_trending' => true,
            'badge_community_pick' => true,
        ]);

        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee('Trending')
            ->assertSee('Community Pick');
    }

    public function test_breakdown_uses_reading_time_from_meta_or_body(): void
    {
        $post = CommunityPost::factory()->create([
            'meta' => ['reading_time' => '5'],
            'body' => '<p>Short body</p>',
        ]);

        $this->assertSame(5.0, CommunityArticleScoreService::readingMinutes($post));
    }
}
