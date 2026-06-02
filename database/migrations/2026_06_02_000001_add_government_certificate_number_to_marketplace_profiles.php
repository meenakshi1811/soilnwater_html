<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('government_certificate_number', 100)->nullable()->after('gst_number');
        });

        Schema::table('consultants', function (Blueprint $table) {
            $table->string('government_certificate_number', 100)->nullable()->after('gst_number');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('government_certificate_number');
        });

        Schema::table('consultants', function (Blueprint $table) {
            $table->dropColumn('government_certificate_number');
        });
    }
};
