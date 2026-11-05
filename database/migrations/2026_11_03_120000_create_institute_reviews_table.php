<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Reviews visitors write on a school's or institute's page (one each, which they can update)
        Schema::create('institute_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->string('relation', 40)->nullable();
            $table->text('review')->nullable();
            $table->timestamps();

            $table->unique(['institute_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institute_reviews');
    }
};
