<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultant_branches', function (Blueprint $table) {
            $table->string('occupation')->nullable()->after('contact_person');
        });
    }

    public function down(): void
    {
        Schema::table('consultant_branches', function (Blueprint $table) {
            $table->dropColumn('occupation');
        });
    }
};
