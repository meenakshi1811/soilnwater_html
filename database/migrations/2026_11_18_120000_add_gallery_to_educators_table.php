<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // The teacher's photo gallery on their public page (paths under public/uploads/educators/gallery)
    public function up(): void
    {
        Schema::table('educators', function (Blueprint $table): void {
            $table->json('gallery')->nullable()->after('certifications');
        });
    }

    public function down(): void
    {
        Schema::table('educators', function (Blueprint $table): void {
            $table->dropColumn('gallery');
        });
    }
};
