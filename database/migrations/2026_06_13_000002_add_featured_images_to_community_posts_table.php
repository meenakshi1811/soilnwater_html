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
            $table->json('featured_images')->nullable()->after('featured_image_path');
        });

        DB::table('community_posts')->orderBy('id')->chunkById(100, function ($posts): void {
            foreach ($posts as $post) {
                if (! filled($post->featured_image_path)) {
                    continue;
                }

                DB::table('community_posts')->where('id', $post->id)->update([
                    'featured_images' => json_encode([$post->featured_image_path]),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->dropColumn('featured_images');
        });
    }
};
