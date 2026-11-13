<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When a school's, college's or institute's public page was last opened by a visitor — shown in
 * the admin lists beside the date and time it registered.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->timestamp('last_viewed_at')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->dropColumn('last_viewed_at');
        });
    }
};
