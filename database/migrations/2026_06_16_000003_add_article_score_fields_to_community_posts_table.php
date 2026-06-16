<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->unsignedInteger('views_count')->default(0)->after('allow_poll');
            $table->unsignedInteger('shares_count')->default(0)->after('views_count');
            $table->decimal('quality_score', 5, 2)->nullable()->after('shares_count');
            $table->decimal('article_score', 8, 2)->default(0)->after('quality_score');
            $table->timestamp('article_score_calculated_at')->nullable()->after('article_score');
            $table->boolean('badge_trending')->default(false)->after('is_highlighted');
            $table->boolean('badge_editors_choice')->default(false)->after('badge_trending');
            $table->boolean('badge_most_read')->default(false)->after('badge_editors_choice');
            $table->boolean('badge_community_pick')->default(false)->after('badge_most_read');
        });
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->dropColumn([
                'views_count',
                'shares_count',
                'quality_score',
                'article_score',
                'article_score_calculated_at',
                'badge_trending',
                'badge_editors_choice',
                'badge_most_read',
                'badge_community_pick',
            ]);
        });
    }
};
