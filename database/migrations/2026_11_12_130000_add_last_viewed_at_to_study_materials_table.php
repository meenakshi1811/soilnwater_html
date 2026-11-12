<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When a study material was last opened on the site — shown beside "Updated" (its last edit) in the
 * dashboards, which a view no longer changes.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_materials', function (Blueprint $table) {
            $table->timestamp('last_viewed_at')->nullable()->after('views_count');
        });
    }

    public function down(): void
    {
        Schema::table('study_materials', function (Blueprint $table) {
            $table->dropColumn('last_viewed_at');
        });
    }
};
