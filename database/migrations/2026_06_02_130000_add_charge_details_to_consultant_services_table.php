<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('consultant_services')) {
            return;
        }

        Schema::table('consultant_services', function (Blueprint $table) {
            if (! Schema::hasColumn('consultant_services', 'consultation_charges')) {
                $table->json('consultation_charges')->nullable()->after('price');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('consultant_services')) {
            return;
        }

        Schema::table('consultant_services', function (Blueprint $table) {
            foreach (['consultation_charges'] as $column) {
                if (Schema::hasColumn('consultant_services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
