<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_homepage_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('context', 32);
            $table->string('image_path', 500);
            $table->timestamps();

            $table->unique(['category_id', 'context']);
            $table->index('context');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_homepage_images');
    }
};
