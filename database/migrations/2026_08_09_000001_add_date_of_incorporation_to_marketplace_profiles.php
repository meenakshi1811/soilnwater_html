<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['vendors', 'consultants', 'service_providers'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->date('date_of_incorporation')->nullable()->after('government_certificate_number');
            });
        }
    }

    public function down(): void
    {
        foreach (['vendors', 'consultants', 'service_providers'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->dropColumn('date_of_incorporation');
            });
        }
    }
};
