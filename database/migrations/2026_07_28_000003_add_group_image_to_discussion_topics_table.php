<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discussion_topics', function (Blueprint $table): void {
            $table->string('group_image')->nullable()->after('is_group');
        });
    }

    public function down(): void
    {
        Schema::table('discussion_topics', function (Blueprint $table): void {
            $table->dropColumn('group_image');
        });
    }
};
