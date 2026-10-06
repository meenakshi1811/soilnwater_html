<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('educators') || Schema::hasColumn('educators', 'associated_with_school')) {
            return;
        }

        Schema::table('educators', function (Blueprint $blueprint) {
            $blueprint->boolean('associated_with_school')->default(true)->after('associated_institute');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('educators') && Schema::hasColumn('educators', 'associated_with_school')) {
            Schema::table('educators', function (Blueprint $blueprint) {
                $blueprint->dropColumn('associated_with_school');
            });
        }
    }
};
