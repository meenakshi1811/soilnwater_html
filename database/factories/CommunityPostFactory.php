<?php

namespace Database\Factories;

use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CommunityPost>
 */
class CommunityPostFactory extends Factory
{
    protected $model = CommunityPost::class;

    public function definition(): array
    {
        $title = fake()->sentence(6);

        return [
            'user_id' => User::factory(),
            'content_type' => 'articles',
            'category' => 'Education',
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1000, 9999),
            'excerpt' => fake()->sentence(12),
            'body' => '<p>'.fake()->paragraphs(3, true).'</p>',
            'location' => 'Jaipur, Rajasthan, India',
            'location_lat' => 26.9124000,
            'location_lng' => 75.7873000,
            'allow_comments' => true,
            'allow_sharing' => true,
            'allow_poll' => false,
            'is_featured' => false,
            'is_sponsored' => false,
            'is_highlighted' => false,
            'status' => CommunityPost::STATUS_PUBLISHED,
            'published_at' => now(),
            'publish_as' => CommunityPost::PUBLISH_AS_PUBLIC_PROFILE,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (): array => [
            'status' => CommunityPost::STATUS_PENDING,
            'published_at' => null,
            'submitted_at' => now(),
        ]);
    }
}
