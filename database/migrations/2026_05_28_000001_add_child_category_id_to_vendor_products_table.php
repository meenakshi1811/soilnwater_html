<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_products', function (Blueprint $table) {
            $table->foreignId('child_category_id')
                ->nullable()
                ->after('subcategory_id')
                ->constrained('categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vendor_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('child_category_id');
        });
    }
};
