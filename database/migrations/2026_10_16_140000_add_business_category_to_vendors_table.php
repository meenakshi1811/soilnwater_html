<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A vendor's business category and optional subcategory, set in Admin → Vendors. Used on homepage
 * cards (label and category card image) when the vendor has no product with a category.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table): void {
            $table->foreignId('business_category_id')->nullable()->after('display_name')->constrained('categories')->nullOnDelete();
            $table->foreignId('business_subcategory_id')->nullable()->after('business_category_id')->constrained('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('business_subcategory_id');
            $table->dropConstrainedForeignId('business_category_id');
        });
    }
};
