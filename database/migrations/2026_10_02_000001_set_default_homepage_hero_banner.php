<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('homepage_settings')
            ->where('id', 1)
            ->update(['hero_banner_image' => 'assets/images/hero-banner-community.jpg']);
    }

    public function down(): void
    {
        // Previous hero paths varied per environment; leave unchanged on rollback.
    }
};
