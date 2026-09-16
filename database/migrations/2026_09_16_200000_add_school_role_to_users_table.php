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

        DB::statement("ALTER TABLE users MODIFY role ENUM('user','vendor','builder','developer','consultant','service_provider','teacher','student','school','institute','admin','employee') NOT NULL DEFAULT 'user'");

        DB::table('users')
            ->where('role', 'institute')
            ->whereIn('id', function ($query): void {
                $query->select('user_id')
                    ->from('institutes')
                    ->whereIn('institution_type', ['school', 'college', 'university']);
            })
            ->update(['role' => 'school']);
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('users')
            ->where('role', 'school')
            ->update(['role' => 'institute']);

        DB::statement("ALTER TABLE users MODIFY role ENUM('user','vendor','builder','developer','consultant','service_provider','teacher','student','institute','admin','employee') NOT NULL DEFAULT 'user'");
    }
};
