<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // If an older build already created the tutor role, merge it into teacher.
        DB::table('users')->where('role', 'tutor')->update(['role' => 'teacher']);

        if (DB::getSchemaBuilder()->hasTable('educators')) {
            DB::table('educators')->where('type', 'tutor')->update(['type' => 'teacher']);
        }

        DB::statement("ALTER TABLE users MODIFY role ENUM('user','vendor','builder','developer','consultant','service_provider','teacher','admin','employee') NOT NULL DEFAULT 'user'");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('user','vendor','builder','developer','consultant','service_provider','teacher','tutor','admin','employee') NOT NULL DEFAULT 'user'");
        }
    }
};
