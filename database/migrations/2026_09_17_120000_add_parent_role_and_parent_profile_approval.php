<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('user','vendor','builder','developer','consultant','service_provider','teacher','student','school','institute','parent','admin','employee') NOT NULL DEFAULT 'user'");
        }

        Schema::table('parent_profiles', function (Blueprint $table): void {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('is_enabled');
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('approved_by');
            $table->boolean('converted_from_user')->default(false)->after('rejection_reason');
        });

        DB::table('parent_profiles')
            ->where('is_enabled', true)
            ->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('parent_profiles', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['status', 'approved_at', 'rejection_reason', 'converted_from_user']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE users SET role = 'user' WHERE role = 'parent'");
            DB::statement("ALTER TABLE users MODIFY role ENUM('user','vendor','builder','developer','consultant','service_provider','teacher','student','school','institute','admin','employee') NOT NULL DEFAULT 'user'");
        }
    }
};
