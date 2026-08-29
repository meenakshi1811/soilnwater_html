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
            'writing_purpose' => 'Personal Experience',
            'title' => 'A Story In Pages',
            'excerpt' => 'A short story told page by page.',
            'status' => CommunityPost::STATUS_DRAFT,
            'location_type' => 'city',
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => '26.9124000',
            'location_lng' => '75.7873000',
            'story_type' => 'Fiction',
            'story_language' => 'English',
            'book_pages' => [
                ['content' => '<p>Once upon a time in a dry village.</p>', 'language' => 'en'],
                ['content' => '<p>बहुत सालों बाद आखिरकार बारिश आई।</p>', 'language' => 'hi'],
            ],
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
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

    public function test_story_publish_accepts_hindi_editor_language_on_book_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->postJson(route('community.posts.store'), [
                'content_type' => 'stories',
                'category' => 'Inspirational Stories',
                'writing_purpose' => 'Personal Experience',
                'title' => 'Hindi Editor Language Story',
                'excerpt' => 'A Hindi story page.',
                'status' => CommunityPost::STATUS_PUBLISHED,
                'location_type' => 'city',
                'location' => 'Jaipur, Rajasthan, India',
                'location_lat' => '26.9124000',
                'location_lng' => '75.7873000',
                'story_type' => 'Fiction',
                'story_language' => 'Hindi',
                'publish_as' => CommunityPost::PUBLISH_AS_PUBLIC_PROFILE,
                'book_pages' => [
                    ['content' => '<p>आज हम तुम्हें एक कहानी सुनाते हैं।</p>', 'language' => 'hindi'],
                ],
                'accept_content_responsibility' => '1',
                'accept_original_work_indemnity' => '1',
            ])
            ->assertOk();

        $post = CommunityPost::query()->where('title', 'Hindi Editor Language Story')->firstOrFail();
        $this->assertSame('hi', $post->bookPages()[0]['language']);
    }

    public function test_autobiography_post_can_be_saved_with_chapters(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('community.posts.store'), [
            'content_type' => 'autobiography',
            'category' => 'Personal Journey',
            'writing_purpose' => 'Personal Experience',
            'title' => 'My Life In Chapters',
            'excerpt' => 'An autobiography told chapter by chapter.',
            'status' => CommunityPost::STATUS_DRAFT,
            'location_type' => 'city',
            'location' => 'Dehradun, Uttarakhand, India',
            'location_lat' => '30.3165000',
            'location_lng' => '78.0322000',
            'autobiography_type' => 'Complete Life Story',
            'author_bio' => 'Community volunteer and lifelong learner.',
            'book_pages' => [
                [
                    'title' => 'Chapter 1 – Childhood',
                    'summary' => 'Born in Dehradun.',
                    'content' => '<p>I grew up near the hills and learned early that water shapes every season.</p>',
                    'language' => 'en',
                ],
                [
                    'title' => 'Chapter 2 – Education',
                    'summary' => 'School and college years.',
                    'content' => '<p>Education opened doors I did not know existed in my village.</p>',
                    'language' => 'en',
                ],
            ],
            'accept_content_responsibility' => '1',
            'accept_original_work_indemnity' => '1',
        ]);

        $response->assertOk();

        $post = CommunityPost::query()->where('title', 'My Life In Chapters')->firstOrFail();

        $this->assertSame('autobiography', $post->content_type);
        $this->assertCount(2, $post->bookPages());
        $this->assertSame('Chapter 1 – Childhood', $post->bookPages()[0]['title']);
        $this->assertSame('Born in Dehradun.', $post->bookPages()[0]['summary']);
        $this->assertStringContainsString('grew up near the hills', $post->body);

        $this->actingAs($user)
            ->get(route('community.show', $post))
            ->assertOk()
            ->assertSee('Chapter 1')
            ->assertSee('Chapter 1 – Childhood')
            ->assertSee('Born in Dehradun.')
            ->assertSee('grew up near the hills');
    }
}
