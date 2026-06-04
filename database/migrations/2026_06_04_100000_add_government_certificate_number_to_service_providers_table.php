<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('service_providers', 'government_certificate_number')) {
            Schema::table('service_providers', function (Blueprint $table) {
                $table->string('government_certificate_number', 100)->nullable()->after('gst_number');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('service_providers', 'government_certificate_number')) {
            Schema::table('service_providers', function (Blueprint $table) {
                $table->dropColumn('government_certificate_number');
            });
        }
    }
};
