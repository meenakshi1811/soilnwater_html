<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->string('approval_status', 20)->default('pending')->after('status')->index();
            $table->timestamp('approval_reviewed_at')->nullable()->after('approval_status');
            $table->foreignId('approval_reviewed_by')->nullable()->after('approval_reviewed_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('approval_reviewed_by');
            $table->dropColumn(['approval_status', 'approval_reviewed_at']);
        });
    }
};
