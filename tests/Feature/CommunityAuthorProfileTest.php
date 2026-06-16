<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CommunityAuthorProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_author_image_on_my_posts_page(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'author_slug' => 'jane-author',
        ]);

        $this->actingAs($user)
            ->patch(route('community.posts.author-url.update'), [
                'author_slug' => 'jane-author',
                'author_image' => $this->testImage('author.png'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $user->refresh();

        $this->assertNotNull($user->author_image);
        $this->assertFileExists(public_path($user->author_image));
    }

    public function test_author_image_url_helper_returns_asset_url(): void
    {
        $user = User::factory()->create([
            'author_image' => 'uploads/users/author-profiles/example.jpg',
        ]);

        $this->assertSame(asset('uploads/users/author-profiles/example.jpg'), $user->authorImageUrl());
    }

    public function test_my_community_posts_page_shows_author_profile_form(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($user)
            ->get(route('community.posts.index'))
            ->assertOk()
            ->assertSee('Author profile')
            ->assertSee('Upload photo')
            ->assertSee('name="author_image"', false);
    }

    private function testImage(string $name): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'author-image-');
        file_put_contents($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=',
            true
        ));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }
}
