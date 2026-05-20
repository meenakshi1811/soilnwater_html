<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_ads', function (Blueprint $table): void {
            $table->json('selected_modules')->nullable()->after('subcategory_id');
        });
    }

    public function down(): void
    {
        Schema::table('user_ads', function (Blueprint $table): void {
            $table->dropColumn('selected_modules');
        });
    }
};
