<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->string('writing_purpose', 120)->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->dropColumn('writing_purpose');
        });
    }
};
