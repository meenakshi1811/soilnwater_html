<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('premium_prices', function (Blueprint $table) {
            $table->id();
            $table->string('profile_type', 32)->unique();
            $table->decimal('amount', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        DB::table('premium_prices')->insert([
            [
                'profile_type' => 'vendor',
                'amount' => 999.00,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'profile_type' => 'service',
                'amount' => 999.00,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'profile_type' => 'consultant',
                'amount' => 999.00,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('premium_prices');
    }
};
