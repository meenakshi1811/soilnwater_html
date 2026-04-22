<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_templates', function (Blueprint $table) {
            $table->id();
            $table->string('size_type', 40)->index();
            $table->string('name', 120);
            $table->string('description', 255)->nullable();
            $table->string('preview_image')->nullable();
            $table->longText('layout_html');
            $table->json('schema_json');
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_templates');
    }
};

