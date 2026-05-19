<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_products', function (Blueprint $table) {
            $table->string('location')->after('shipping_charges');
            $table->decimal('latitude', 10, 7)->after('location');
            $table->decimal('longitude', 10, 7)->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('vendor_products', function (Blueprint $table) {
            $table->dropColumn(['location', 'latitude', 'longitude']);
        });
    }
};
