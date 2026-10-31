<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A photo for each facility, by facility name ({"Library": "uploads/institutes/facilities/..."})
        Schema::table('institutes', function (Blueprint $table) {
            $table->json('facility_images')->nullable()->after('facilities');
        });
    }

    public function down(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->dropColumn('facility_images');
        });
    }
};
