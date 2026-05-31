<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE vendors MODIFY hero_sub_heading LONGTEXT NULL');

            return;
        }

        Schema::table('vendors', function (Blueprint $table) {
            $table->longText('hero_sub_heading')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE vendors MODIFY hero_sub_heading TEXT NULL');

            return;
        }

        Schema::table('vendors', function (Blueprint $table) {
            $table->text('hero_sub_heading')->nullable()->change();
        });
    }
};
