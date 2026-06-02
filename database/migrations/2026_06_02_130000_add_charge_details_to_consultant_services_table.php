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

            if (! Schema::hasColumn('consultant_services', 'charges_detail')) {
                $table->text('charges_detail')->nullable()->after('consultation_charges');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('consultant_services')) {
            return;
        }

        Schema::table('consultant_services', function (Blueprint $table) {
            foreach (['charges_detail', 'consultation_charges'] as $column) {
                if (Schema::hasColumn('consultant_services', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
