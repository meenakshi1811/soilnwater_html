<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table): void {
            $table->string('offers_market_banner_image')->nullable()->after('hero_banner_image');
            $table->string('ads_market_banner_image')->nullable()->after('offers_market_banner_image');
        });
    }

    public function down(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table): void {
            $table->dropColumn(['offers_market_banner_image', 'ads_market_banner_image']);
        });
    }
};
