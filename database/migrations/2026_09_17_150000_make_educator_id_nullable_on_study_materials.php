<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_materials', function (Blueprint $table): void {
            $table->dropForeign(['educator_id']);
        });

        Schema::table('study_materials', function (Blueprint $table): void {
            $table->foreignId('educator_id')->nullable()->change();
            $table->foreign('educator_id')->references('id')->on('educators')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('study_materials', function (Blueprint $table): void {
            $table->dropForeign(['educator_id']);
        });

        Schema::table('study_materials', function (Blueprint $table): void {
            $table->foreignId('educator_id')->nullable(false)->change();
            $table->foreign('educator_id')->references('id')->on('educators')->cascadeOnDelete();
        });
    }
};
