<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->text('hero_main_heading')->nullable()->change();
            $table->text('hero_sub_heading')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('hero_main_heading')->nullable()->change();
            $table->string('hero_sub_heading')->nullable()->change();
        });
    }
};
