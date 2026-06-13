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
            $table->string('location', 160)->nullable()->after('tags');
            $table->decimal('location_lat', 10, 7)->nullable()->after('location');
            $table->decimal('location_lng', 10, 7)->nullable()->after('location_lat');
        });

        DB::table('community_posts')->orderBy('id')->chunkById(100, function ($posts): void {
            foreach ($posts as $post) {
                $meta = json_decode($post->meta ?? '{}', true);
                if (! is_array($meta)) {
                    continue;
                }

                $location = data_get($meta, 'location');
                $lat = data_get($meta, 'location_lat');
                $lng = data_get($meta, 'location_lng');

                if (! filled($location) && ! filled($lat) && ! filled($lng)) {
                    continue;
                }

                DB::table('community_posts')->where('id', $post->id)->update([
                    'location' => $location,
                    'location_lat' => filled($lat) ? $lat : null,
                    'location_lng' => filled($lng) ? $lng : null,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->dropColumn(['location', 'location_lat', 'location_lng']);
        });
    }
};
