<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->json('hero_main_style')->nullable()->after('hero_main_heading');
            $table->json('hero_sub_style')->nullable()->after('hero_sub_heading');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['hero_main_style', 'hero_sub_style']);
        });
    }
};
