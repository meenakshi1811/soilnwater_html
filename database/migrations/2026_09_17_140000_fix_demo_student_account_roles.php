<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereIn('email', [
                'student1.demo@soilnwater.test',
                'student2.demo@soilnwater.test',
                'student3.demo@soilnwater.test',
            ])
            ->where('role', 'user')
            ->update(['role' => 'student']);
    }

    public function down(): void
    {
        DB::table('users')
            ->whereIn('email', [
                'student1.demo@soilnwater.test',
                'student2.demo@soilnwater.test',
                'student3.demo@soilnwater.test',
            ])
            ->where('role', 'student')
            ->update(['role' => 'user']);
    }
};
