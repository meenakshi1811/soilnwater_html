<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // All of a school's contact emails, the first being `email` (like phone_numbers and phone)
        Schema::table('institutes', function (Blueprint $table) {
            $table->json('emails')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->dropColumn('emails');
        });
    }
};
