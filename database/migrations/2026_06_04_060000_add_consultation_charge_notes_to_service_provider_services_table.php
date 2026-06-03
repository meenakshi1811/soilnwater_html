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
            if (! Schema::hasColumn('service_provider_services', 'consultation_charge_notes')) {
                $table->json('consultation_charge_notes')->nullable()->after('consultation_charges');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('service_provider_services') || ! Schema::hasColumn('service_provider_services', 'consultation_charge_notes')) {
            return;
        }

        Schema::table('service_provider_services', function (Blueprint $table) {
            $table->dropColumn('consultation_charge_notes');
        });
    }
};
