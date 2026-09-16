<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('child_profiles', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('phone_number');
            $table->unsignedTinyInteger('age')->nullable()->after('date_of_birth');
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('child_profiles', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth', 'age']);
            $table->string('email')->nullable(false)->change();
        });
    }
};
