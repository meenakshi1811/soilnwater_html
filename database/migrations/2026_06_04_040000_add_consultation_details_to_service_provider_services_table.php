<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('service_provider_services')) {
            return;
        }

        Schema::table('service_provider_services', function (Blueprint $table) {
            if (! Schema::hasColumn('service_provider_services', 'consultation_type')) {
                $table->string('consultation_type', 20)->default('offline')->after('description');
            }

            if (! Schema::hasColumn('service_provider_services', 'business_type')) {
                $table->string('business_type')->nullable()->after('consultation_type');
            }

            if (! Schema::hasColumn('service_provider_services', 'service_area')) {
                $table->text('service_area')->nullable()->after('business_type');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_provider_services')) {
            return;
        }

        Schema::table('service_provider_services', function (Blueprint $table) {
            foreach (['service_area', 'business_type', 'consultation_type'] as $column) {
                if (Schema::hasColumn('service_provider_services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
