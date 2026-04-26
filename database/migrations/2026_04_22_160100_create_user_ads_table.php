<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_ads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ad_template_id')->constrained('ad_templates')->cascadeOnDelete();
            $table->string('size_type', 40)->index();
            $table->string('title', 140);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('pending')->index();
            $table->json('fields_json')->nullable();
            $table->longText('rendered_html')->nullable();
            $table->string('final_image')->nullable();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('review_note', 400)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_ads');
    }
};

