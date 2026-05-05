<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_sizes', function (Blueprint $table): void {
            $table->id();
            $table->string('size_key', 60)->unique();
            $table->string('name', 120);
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->boolean('admin_only')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_sizes');
    }
};
