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
            if (! Schema::hasColumn('service_provider_services', 'postal_code')) {
                $table->string('postal_code', 20)->nullable()->after('location');
            }

            if (! Schema::hasColumn('service_provider_services', 'city')) {
                $table->string('city', 120)->nullable()->after('postal_code');
            }

            if (! Schema::hasColumn('service_provider_services', 'service_radius')) {
                $table->unsignedInteger('service_radius')->nullable()->after('city');
            }

            if (! Schema::hasColumn('service_provider_services', 'working_hours')) {
                $table->text('working_hours')->nullable()->after('service_radius');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_provider_services')) {
            return;
        }

        Schema::table('service_provider_services', function (Blueprint $table) {
            foreach (['working_hours', 'service_radius', 'city', 'postal_code'] as $column) {
                if (Schema::hasColumn('service_provider_services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
