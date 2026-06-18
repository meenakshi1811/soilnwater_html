<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_post_star_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->timestamps();

            $table->unique(['community_post_id', 'user_id']);
        });

        Schema::table('community_posts', function (Blueprint $table) {
            $table->boolean('badge_most_shared')->default(false)->after('badge_most_read');
            $table->boolean('badge_most_inspiring')->default(false)->after('badge_most_shared');
            $table->boolean('badge_community_favorite')->default(false)->after('badge_most_inspiring');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_post_star_ratings');

        Schema::table('community_posts', function (Blueprint $table) {
            $table->dropColumn([
                'badge_most_shared',
                'badge_most_inspiring',
                'badge_community_favorite',
            ]);
        });
    }
};
