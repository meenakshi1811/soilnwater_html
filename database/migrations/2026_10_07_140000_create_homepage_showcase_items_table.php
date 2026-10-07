<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_showcase_items', function (Blueprint $table) {
            $table->id();
            $table->string('type', 32);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->string('link_url', 500);
            $table->string('location')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->unsignedInteger('review_count')->nullable();
            $table->string('category_label')->nullable();
            $table->string('category_tone', 32)->default('slate');
            $table->string('category_icon', 64)->default('fa-store');
            $table->string('discount_badge')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('headline')->nullable();
            $table->string('subheadline')->nullable();
            $table->string('promo_badge')->nullable();
            $table->string('promo_sub')->nullable();
            $table->string('strip_primary')->nullable();
            $table->string('strip_secondary')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_showcase_items');
    }
};
