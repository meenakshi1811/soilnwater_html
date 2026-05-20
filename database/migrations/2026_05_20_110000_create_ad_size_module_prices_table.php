<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_size_module_prices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ad_size_id')->constrained('ad_sizes')->cascadeOnDelete();
            $table->string('module_key', 60);
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            $table->unique(['ad_size_id', 'module_key']);
        });

        if (Schema::hasColumn('ad_sizes', 'module_key') && Schema::hasColumn('ad_sizes', 'module_price')) {
            $rows = DB::table('ad_sizes')->whereNotNull('module_key')->whereNotNull('module_price')->get(['id','module_key','module_price']);
            foreach ($rows as $row) {
                DB::table('ad_size_module_prices')->insert([
                    'ad_size_id' => $row->id,
                    'module_key' => $row->module_key,
                    'amount' => $row->module_price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_size_module_prices');
    }
};
