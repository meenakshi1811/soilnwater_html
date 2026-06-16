<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->string('location_type', 32)->default('global')->after('tags');
        });

        DB::table('community_posts')->orderBy('id')->chunkById(100, function ($posts): void {
            foreach ($posts as $post) {
                $locationType = filled($post->location) || filled($post->location_lat) || filled($post->location_lng)
                    ? 'city'
                    : 'global';

                DB::table('community_posts')->where('id', $post->id)->update([
                    'location_type' => $locationType,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->dropColumn('location_type');
        });
    }
};
