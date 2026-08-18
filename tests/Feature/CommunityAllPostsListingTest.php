<?php

namespace Tests\Feature;

use App\Models\CommunityCategorySubscription;
use App\Models\CommunityPost;
use App\Models\CommunityPostReaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityAllPostsListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_all_from_hub_portal_links_to_all_posts_page(): void
    {
        $this->get(route('community.index', ['hub' => 'knowledge-news']))
            ->assertOk()
            ->assertSee('community-news-portal', false)
            ->assertSee(route('community.all', ['hub' => 'knowledge-news']), false)
            ->assertSee('View All Knowledge &amp; News', false);
    }

    public function test_all_posts_page_renders_boxed_cards_for_the_selected_type(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $article = CommunityPost::factory()->create([
            'user_id' => $user->id,
            'content_type' => 'articles',
            'title' => 'Boxed Article Listing Post',
        ]);
        $story = CommunityPost::factory()->create([
            'user_id' => $user->id,
            'content_type' => 'stories',
            'title' => 'Story Should Stay Off Knowledge Hub',
        ]);

        $this->get(route('community.all', ['hub' => 'knowledge-news', 'type' => 'articles']))
            ->assertOk()
            ->assertSee('community-post-card', false)
            ->assertSee('communityPostsGrid', false)
            ->assertSee('All Articles Posts')
            ->assertSee($article->title)
            ->assertDontSee('community-news-portal', false)
            ->assertDontSee($story->title);
    }

    public function test_all_posts_page_ajax_pagination_returns_boxed_card_html(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        CommunityPost::factory()->count(12)->create([
            'user_id' => $user->id,
            'content_type' => 'articles',
            'published_at' => now()->subDay(),
        ]);

        $latest = CommunityPost::factory()->create([
            'user_id' => $user->id,
            'content_type' => 'articles',
            'title' => 'Thirteenth Knowledge Article',
            'published_at' => now(),
        ]);

        $this->get(route('community.all', ['hub' => 'knowledge-news', 'type' => 'articles']))
            ->assertOk()
            ->assertSee($latest->title)
            ->assertSee('Load more posts');

        $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('community.all', [
                'hub' => 'knowledge-news',
                'type' => 'articles',
                'page' => 2,
            ]))
            ->assertOk()
            ->assertJsonPath('layout', 'grid')
            ->assertJsonFragment(['total' => 13])
            ->assertSee('community-post-card', false);
    }

    public function test_community_home_shows_latest_posts_from_all_types_in_boxed_layout(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $article = CommunityPost::factory()->create([
            'user_id' => $user->id,
            'content_type' => 'articles',
            'title' => 'Home Feed Article',
        ]);
        $story = CommunityPost::factory()->create([
            'user_id' => $user->id,
            'content_type' => 'stories',
            'title' => 'Home Feed Story',
        ]);

        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee('community-post-card', false)
            ->assertSee('communityPostsGrid', false)
            ->assertSee('Latest community posts')
            ->assertSee($article->title)
            ->assertSee($story->title)
            ->assertDontSee('community-news-portal', false)
            ->assertDontSee('Latest Knowledge &amp; News', false);
    }

    public function test_community_home_highlights_most_liked_subscribed_and_read_posts(): void
    {
        $author = User::factory()->create(['email_verified_at' => now()]);
        $reader = User::factory()->create(['email_verified_at' => now()]);

        $liked = CommunityPost::factory()->create([
            'user_id' => $author->id,
            'content_type' => 'stories',
            'title' => 'Highlight Liked Post',
            'views_count' => 1,
        ]);
        $subscribed = CommunityPost::factory()->create([
            'user_id' => $author->id,
            'content_type' => 'articles',
            'category' => 'Education',
            'title' => 'Highlight Subscribed Post',
            'views_count' => 2,
        ]);
        $read = CommunityPost::factory()->create([
            'user_id' => $author->id,
            'content_type' => 'reports',
            'title' => 'Highlight Read Post',
            'views_count' => 80,
        ]);

        CommunityPostReaction::query()->create([
            'community_post_id' => $liked->id,
            'user_id' => $reader->id,
            'reaction' => 'Like',
        ]);

        CommunityCategorySubscription::query()->create([
            'user_id' => $reader->id,
            'content_type' => 'articles',
            'category' => 'Education',
        ]);

        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee($liked->title)
            ->assertSee($subscribed->title)
            ->assertSee($read->title)
            ->assertSee('Most Liked')
            ->assertSee('Most Subscribed')
            ->assertSee('Most Read')
            ->assertSee('community-post-card--spotlight', false);
    }

    public function test_community_home_ajax_pagination_returns_boxed_card_html(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        CommunityPost::factory()->count(12)->create([
            'user_id' => $user->id,
            'content_type' => 'stories',
            'published_at' => now()->subDay(),
        ]);

        $latest = CommunityPost::factory()->create([
            'user_id' => $user->id,
            'content_type' => 'articles',
            'title' => 'Newest Mixed Type Post',
            'published_at' => now(),
        ]);

        $this->get(route('community.index'))
            ->assertOk()
            ->assertSee($latest->title)
            ->assertSee('Load more posts');

        $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('community.index', ['page' => 2]))
            ->assertOk()
            ->assertJsonPath('layout', 'grid')
            ->assertJsonFragment(['total' => 13])
            ->assertSee('community-post-card', false);
    }
}
