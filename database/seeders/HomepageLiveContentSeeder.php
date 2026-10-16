<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Recreates the live homepage content (October 2026) on another database:
 * php artisan db:seed --class=HomepageLiveContentSeeder --force
 */
class HomepageLiveContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            HomepageLiveShowcaseSeeder::class,
            HomepageLiveSectionOrderSeeder::class,
            HomepageStudyMaterialCardSeeder::class,
        ]);
    }
}
