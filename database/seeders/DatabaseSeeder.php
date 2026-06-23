<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ModulePermissionSeeder::class);
        $this->call(AdSizeSeeder::class);
        $this->call(AdminOnlyAdSizeSeeder::class);
        $this->call(AdTemplateSeeder::class);

        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(CommunityPostSeeder::class);
        $this->call(CommunityBookPostSeeder::class);
        $this->call(CommunityShowcasePostSeeder::class);
        $this->call(CommunityAwarenessPostSeeder::class);
        $this->call(CommunityBusinessPostSeeder::class);
        $this->call(CommunityWomensWorldPostSeeder::class);
        $this->call(CommunitySeniorCitizensForumPostSeeder::class);
        $this->call(CommunityChildrensCornerPostSeeder::class);
    }
}
