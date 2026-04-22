<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string('location')->nullable()->after('short_description');
            $table->decimal('location_lat', 10, 7)->nullable()->after('location');
            $table->decimal('location_lng', 10, 7)->nullable()->after('location_lat');
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn(['location', 'location_lat', 'location_lng']);
        });
    }
};
