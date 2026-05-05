<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_ads', function (Blueprint $table) {
            $table->date('valid_until')->nullable()->after('final_image')->index();
        });
    }

    public function down(): void
    {
        Schema::table('user_ads', function (Blueprint $table) {
            $table->dropColumn('valid_until');
        });
    }
};
