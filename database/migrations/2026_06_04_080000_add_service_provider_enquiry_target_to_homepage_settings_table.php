<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table): void {
            $table->string('service_provider_enquiry_send_to', 20)->default('all')->after('vendor_enquiry_send_to');
        });
    }

    public function down(): void
    {
        Schema::table('homepage_settings', function (Blueprint $table): void {
            $table->dropColumn('service_provider_enquiry_send_to');
        });
    }
};
