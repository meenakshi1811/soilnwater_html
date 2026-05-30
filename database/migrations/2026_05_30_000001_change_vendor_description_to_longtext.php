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
            DB::statement('ALTER TABLE vendors MODIFY description LONGTEXT NULL');

            return;
        }

        Schema::table('vendors', function (Blueprint $table) {
            $table->longText('description')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE vendors MODIFY description TEXT NULL');

            return;
        }

        Schema::table('vendors', function (Blueprint $table) {
            $table->text('description')->nullable()->change();
        });
    }
};
