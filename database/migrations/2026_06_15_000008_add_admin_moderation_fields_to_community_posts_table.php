<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->boolean('is_featured')->default(false)->after('allow_poll');
            $table->boolean('is_sponsored')->default(false)->after('is_featured');
            $table->boolean('is_highlighted')->default(false)->after('is_sponsored');
        });
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->dropColumn(['is_featured', 'is_sponsored', 'is_highlighted']);
        });
    }
};
