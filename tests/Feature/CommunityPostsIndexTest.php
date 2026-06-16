<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityPostsIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_posts_index_page_renders(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        CommunityPost::factory()->create([
            'user_id' => $user->id,
            'status' => CommunityPost::STATUS_PENDING,
            'published_at' => null,
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('community.posts.index'))
            ->assertOk()
            ->assertSee('My Community Posts')
            ->assertSee('myCommunityPostsTable');
    }

    public function test_my_posts_data_endpoint_returns_posts(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        CommunityPost::factory()->create([
            'user_id' => $user->id,
            'title' => 'Visible In DataTable',
            'status' => CommunityPost::STATUS_PENDING,
            'published_at' => null,
            'submitted_at' => now(),
        ]);

        $this->actingAs($user)
            ->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->getJson(route('community.posts.data'))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Visible In DataTable']);
    }
}
