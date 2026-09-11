<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('educators', function (Blueprint $table) {
            $table->text('tuition_point_address')->nullable()->after('tuition_location');
            $table->string('tuition_place_id')->nullable()->after('tuition_point_address');
            $table->decimal('tuition_latitude', 10, 7)->nullable()->after('tuition_place_id');
            $table->decimal('tuition_longitude', 10, 7)->nullable()->after('tuition_latitude');
        });
    }

    public function down(): void
    {
        Schema::table('educators', function (Blueprint $table) {
            $table->dropColumn([
                'tuition_point_address',
                'tuition_place_id',
                'tuition_latitude',
                'tuition_longitude',
            ]);
        });
    }
};
