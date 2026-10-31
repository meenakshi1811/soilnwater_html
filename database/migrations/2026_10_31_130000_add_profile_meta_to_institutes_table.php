<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Extra public-profile settings the school page reads (SchoolProfilePresenter::meta), e.g.
        // {"course_wings": [...], "streams": [...]}
        Schema::table('institutes', function (Blueprint $table) {
            $table->json('profile_meta')->nullable()->after('gallery');
        });
    }

    public function down(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->dropColumn('profile_meta');
        });
    }
};
