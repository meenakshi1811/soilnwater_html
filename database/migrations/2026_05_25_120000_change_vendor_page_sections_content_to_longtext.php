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
            DB::statement('ALTER TABLE vendor_page_sections MODIFY content LONGTEXT NULL');

            return;
        }

        Schema::table('vendor_page_sections', function (Blueprint $table) {
            $table->longText('content')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE vendor_page_sections MODIFY content TEXT NULL');

            return;
        }

        Schema::table('vendor_page_sections', function (Blueprint $table) {
            $table->text('content')->nullable()->change();
        });
    }
};
