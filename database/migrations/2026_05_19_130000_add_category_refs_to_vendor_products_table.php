<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vendor_products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('description')->constrained('categories')->nullOnDelete();
            $table->foreignId('subcategory_id')->nullable()->after('category_id')->constrained('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vendor_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subcategory_id');
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
