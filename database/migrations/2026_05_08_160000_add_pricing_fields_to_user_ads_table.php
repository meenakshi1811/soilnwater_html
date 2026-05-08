<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_ads', function (Blueprint $table) {
            $table->decimal('base_price_per_day', 10, 2)->nullable()->after('is_sponsored');
            $table->unsignedInteger('total_days')->nullable()->after('base_price_per_day');
            $table->decimal('subtotal', 12, 2)->nullable()->after('total_days');
            $table->decimal('gst_rate', 5, 2)->nullable()->after('subtotal');
            $table->decimal('gst_amount', 12, 2)->nullable()->after('gst_rate');
            $table->decimal('grand_total', 12, 2)->nullable()->after('gst_amount');
        });
    }

    public function down(): void
    {
        Schema::table('user_ads', function (Blueprint $table) {
            $table->dropColumn([
                'base_price_per_day',
                'total_days',
                'subtotal',
                'gst_rate',
                'gst_amount',
                'grand_total',
            ]);
        });
    }
};
