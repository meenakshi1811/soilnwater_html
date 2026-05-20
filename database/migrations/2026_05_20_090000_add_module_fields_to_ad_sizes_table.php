<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ad_sizes', function (Blueprint $table): void {
            $table->string('module_key', 60)->nullable()->after('height');
            $table->decimal('module_price', 10, 2)->nullable()->after('module_key');
        });
    }

    public function down(): void
    {
        Schema::table('ad_sizes', function (Blueprint $table): void {
            $table->dropColumn(['module_key', 'module_price']);
        });
    }
};
