<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->string('publish_as', 32)->nullable()->after('location_type');
            $table->string('pen_name', 120)->nullable()->after('publish_as');
        });

        DB::table('community_posts')->update([
            'publish_as' => 'public_profile',
        ]);
    }

    public function down(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->dropColumn(['publish_as', 'pen_name']);
        });
    }
};
