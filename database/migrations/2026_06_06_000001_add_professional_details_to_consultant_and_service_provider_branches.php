<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultant_branches', function (Blueprint $table) {
            $table->text('professional_experience')->nullable()->after('occupation');
            $table->text('services_offered')->nullable()->after('professional_experience');
        });

        Schema::table('service_provider_branches', function (Blueprint $table) {
            $table->text('professional_experience')->nullable()->after('occupation');
            $table->text('services_offered')->nullable()->after('professional_experience');
        });
    }

    public function down(): void
    {
        Schema::table('consultant_branches', function (Blueprint $table) {
            $table->dropColumn(['professional_experience', 'services_offered']);
        });

        Schema::table('service_provider_branches', function (Blueprint $table) {
            $table->dropColumn(['professional_experience', 'services_offered']);
        });
    }
};
