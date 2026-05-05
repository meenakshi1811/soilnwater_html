<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('categories')
                  ->nullOnDelete();

            $table->foreignId('subcategory_id')
                  ->nullable()
                  ->constrained('categories')
                  ->nullOnDelete();

            $table->string('title');
            $table->string('discount_tag');
            $table->string('coupon_code')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('banner_image');
            $table->text('short_description')->nullable();

            $table->enum('status', ['active', 'inactive'])->default('inactive');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};