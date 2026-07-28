<?php

namespace Database\Factories;

use App\Models\DiscussionTopic;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DiscussionTopic>
 */
class DiscussionTopicFactory extends Factory
{
    protected $model = DiscussionTopic::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(6),
            'body' => fake()->optional()->paragraph(),
            'is_pinned' => false,
            'replies_count' => 0,
        ];
    }

    public function pinned(): static
    {
        return $this->state(fn () => [
            'is_pinned' => true,
            'pinned_at' => now(),
        ]);
    }

    public function group(): static
    {
        return $this->state(fn () => [
            'is_group' => true,
        ]);
    }
}
