<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_ads', function (Blueprint $table): void {
            $table->json('selected_category_ids')->nullable()->after('selected_modules');
            $table->json('selected_subcategory_ids')->nullable()->after('selected_category_ids');
        });
    }

    public function down(): void
    {
        Schema::table('user_ads', function (Blueprint $table): void {
            $table->dropColumn(['selected_category_ids', 'selected_subcategory_ids']);
        });
    }
};

