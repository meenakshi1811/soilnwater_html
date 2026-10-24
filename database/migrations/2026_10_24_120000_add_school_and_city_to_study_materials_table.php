<?php

use App\Models\StudyMaterial;
use App\Support\StudyMaterialLocation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_materials', function (Blueprint $table) {
            $table->string('school_name', 190)->nullable()->after('board_university')->index();
            $table->string('city', 120)->nullable()->after('school_name')->index();
            $table->string('state', 120)->nullable()->after('city');
            $table->decimal('latitude', 10, 7)->nullable()->after('state');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        // Existing materials: work out their school and city now
        StudyMaterialLocation::flush();
        StudyMaterial::query()->with(['educator', 'user'])->orderBy('id')->chunkById(200, function ($materials) {
            foreach ($materials as $material) {
                StudyMaterialLocation::fill($material);
                $material->saveQuietly();
            }
        });
    }

    public function down(): void
    {
        Schema::table('study_materials', function (Blueprint $table) {
            $table->dropIndex(['school_name']);
            $table->dropIndex(['city']);
            $table->dropColumn(['school_name', 'city', 'state', 'latitude', 'longitude']);
        });
    }
};
