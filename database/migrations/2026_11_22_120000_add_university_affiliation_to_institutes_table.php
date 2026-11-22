<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Colleges and institutes are affiliated to a university, not a board: they get their own field.
 * Schools keep board_affiliation (CBSE, ICSE, State Board…).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->string('university_affiliation')->nullable()->after('board_affiliation');
        });
    }

    public function down(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->dropColumn('university_affiliation');
        });
    }
};
