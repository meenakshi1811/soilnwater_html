<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['vendors', 'consultants'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('public_page_status', 20)->default('draft')->after('status');
                $table->json('pending_page_data')->nullable()->after('public_page_status');
                $table->json('published_page_data')->nullable()->after('pending_page_data');
                $table->timestamp('public_page_submitted_at')->nullable()->after('published_page_data');
                $table->timestamp('public_page_approved_at')->nullable()->after('public_page_submitted_at');
                $table->foreignId('public_page_approved_by')->nullable()->after('public_page_approved_at')->constrained('users')->nullOnDelete();
            });

            DB::table($tableName)
                ->where('status', 'approved')
                ->update(['public_page_status' => 'approved']);
        }
    }

    public function down(): void
    {
        foreach (['vendors', 'consultants'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('public_page_approved_by');
                $table->dropColumn([
                    'public_page_status',
                    'pending_page_data',
                    'published_page_data',
                    'public_page_submitted_at',
                    'public_page_approved_at',
                ]);
            });
        }
    }
};
