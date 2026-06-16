<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE community_posts MODIFY body LONGTEXT NOT NULL');

            return;
        }

        Schema::table('community_posts', function (Blueprint $table) {
            $table->longText('body')->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE community_posts MODIFY body TEXT NOT NULL');

            return;
        }

        Schema::table('community_posts', function (Blueprint $table) {
            $table->text('body')->change();
        });
    }
};
