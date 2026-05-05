<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'employee_role')) {
                $table->dropColumn('employee_role');
            }
            if (Schema::hasColumn('users', 'permissions')) {
                $table->dropColumn('permissions');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_role', 50)->nullable()->after('role');
            $table->json('permissions')->nullable()->after('employee_role');
        });
    }
};
