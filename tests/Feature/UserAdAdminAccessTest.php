<?php

namespace Tests\Feature;

use App\Models\AdSize;
use App\Models\Category;
use App\Models\User;
use App\Models\UserAd;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAdAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_another_users_ad_edit_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $owner = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        AdSize::query()->create([
            'size_key' => 'square',
            'name' => 'Square',
            'width' => 640,
            'height' => 640,
            'is_active' => true,
        ]);

        $category = Category::query()->create([
            'name' => 'Farm Equipment',
            'modules' => ['vendors'],
        ]);
        $subcategory = Category::query()->create([
            'name' => 'Tractors',
            'parent_id' => $category->id,
            'modules' => ['vendors'],
        ]);

        $ad = UserAd::query()->create([
            'user_id' => $owner->id,
            'size_type' => 'square',
            'title' => 'Owner Ad',
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'selected_category_ids' => [$category->id],
            'selected_subcategory_ids' => [$subcategory->id],
            'selected_modules' => ['vendors'],
            'location' => 'Test Location',
            'location_lat' => 12.3456789,
            'location_lng' => 77.1234567,
            'status' => 'pending',
            'valid_until' => now()->addWeek()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->get(route('ads.edit', $ad))
            ->assertOk()
            ->assertSee('Owner Ad');
    }

    public function test_regular_user_still_cannot_open_another_users_ad_edit_page(): void
    {
        $owner = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
        $otherUser = User::factory()->create([
            'role' => 'user',
            'email_verified_at' => now(),
        ]);

        $ad = UserAd::query()->create([
            'user_id' => $owner->id,
            'size_type' => 'square',
            'title' => 'Owner Ad',
            'status' => 'pending',
        ]);

        $this->actingAs($otherUser)
            ->get(route('ads.edit', $ad))
            ->assertNotFound();
    }
}
