<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vendor_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('sku')->nullable();
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('colors')->nullable();
            $table->string('sizes')->nullable();
            $table->decimal('base_price', 12, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('final_price', 12, 2);
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->decimal('shipping_charges', 12, 2)->default(0);
            $table->json('specs')->nullable();
            $table->json('bulk_tiers')->nullable();
            $table->json('images')->nullable();
            $table->string('video_file')->nullable();
            $table->string('youtube_link')->nullable();
            $table->boolean('is_online_sale')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_products');
    }
};
