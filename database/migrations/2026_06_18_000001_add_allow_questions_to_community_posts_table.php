<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('community_posts', 'allow_questions')) {
                $table->boolean('allow_questions')->default(true)->after('allow_comments');
            }
        });
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            if (Schema::hasColumn('community_posts', 'allow_questions')) {
                $table->dropColumn('allow_questions');
            }
        });
    }
};
