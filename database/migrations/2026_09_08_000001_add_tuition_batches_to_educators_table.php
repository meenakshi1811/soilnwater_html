<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('educators', function (Blueprint $table) {
            $table->json('tuition_batches')->nullable()->after('tuition_types');
        });
    }

    public function down(): void
    {
        Schema::table('educators', function (Blueprint $table) {
            $table->dropColumn('tuition_batches');
        });
    }
};
