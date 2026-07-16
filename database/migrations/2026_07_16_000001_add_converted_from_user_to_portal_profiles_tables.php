<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->boolean('converted_from_user')->default(false)->after('status');
        });

        Schema::table('consultants', function (Blueprint $table) {
            $table->boolean('converted_from_user')->default(false)->after('status');
        });

        Schema::table('service_providers', function (Blueprint $table) {
            $table->boolean('converted_from_user')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('converted_from_user');
        });

        Schema::table('consultants', function (Blueprint $table) {
            $table->dropColumn('converted_from_user');
        });

        Schema::table('service_providers', function (Blueprint $table) {
            $table->dropColumn('converted_from_user');
        });
    }
};
