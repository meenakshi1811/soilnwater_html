<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_product_inquiries', function (Blueprint $table) {
            $table->dropForeign(['vendor_product_id']);
            $table->unsignedBigInteger('vendor_product_id')->nullable()->change();
            $table->foreign('vendor_product_id')->references('id')->on('vendor_products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vendor_product_inquiries', function (Blueprint $table) {
            $table->dropForeign(['vendor_product_id']);
            $table->unsignedBigInteger('vendor_product_id')->nullable(false)->change();
            $table->foreign('vendor_product_id')->references('id')->on('vendor_products')->cascadeOnDelete();
        });
    }
};
