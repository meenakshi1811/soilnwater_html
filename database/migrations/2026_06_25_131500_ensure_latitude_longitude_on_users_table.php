<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('pincode');
            }

            if (! Schema::hasColumn('users', 'longitude')) {
                $after = Schema::hasColumn('users', 'latitude') ? 'latitude' : 'pincode';
                $table->decimal('longitude', 10, 7)->nullable()->after($after);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('users', 'longitude')) {
                $columns[] = 'longitude';
            }

            if (Schema::hasColumn('users', 'latitude')) {
                $columns[] = 'latitude';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
