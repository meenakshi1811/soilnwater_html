<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityBookLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_story_post_can_be_saved_with_book_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'stories',
            'category' => 'Inspirational Stories',
            'title' => 'A Story In Pages',
            'excerpt' => 'A short story told page by page.',
            'status' => CommunityPost::STATUS_DRAFT,
            'location_type' => 'city',
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
            'story_genre' => 'Inspirational',
            'book_pages' => [
                ['content' => '<p>Once upon a time in a dry village.</p>', 'language' => 'en'],
                ['content' => '<p>बहुत सालों बाद आखिरकार बारिश आई।</p>', 'language' => 'hi'],
            ],
        ]);

        $response->assertOk();

        $post = CommunityPost::query()->where('title', 'A Story In Pages')->firstOrFail();

        $this->assertSame('stories', $post->content_type);
        $this->assertCount(2, $post->bookPages());
        $this->assertSame('hi', $post->bookPages()[1]['language']);
        $this->assertStringContainsString('Once upon a time', $post->body);
        $this->assertStringContainsString('बहुत सालों बाद', $post->body);

        $this->actingAs($user)
            ->get(route('community.posts.show', $post))
            ->assertOk()
            ->assertSee('Page 1')
            ->assertSee('Page 2')
            ->assertSee('Once upon a time in a dry village.')
            ->assertSee('बहुत सालों बाद');
    }
}
