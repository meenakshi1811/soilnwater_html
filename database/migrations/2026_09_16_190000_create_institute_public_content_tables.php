<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institute_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('message');
            $table->date('expires_at');
            $table->timestamps();
        });

        Schema::create('institute_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('institute_top_performers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('student_name');
            $table->string('class_name')->nullable();
            $table->string('achievement_title');
            $table->string('score')->nullable();
            $table->unsignedTinyInteger('rank')->nullable();
            $table->string('academic_year')->nullable();
            $table->string('photo')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('institute_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('section')->nullable();
            $table->string('class_teacher')->nullable();
            $table->unsignedSmallInteger('strength')->nullable();
            $table->string('room')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('institute_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('author');
            $table->string('class_name')->nullable();
            $table->string('subject')->nullable();
            $table->string('publisher')->nullable();
            $table->string('isbn')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institute_books');
        Schema::dropIfExists('institute_classes');
        Schema::dropIfExists('institute_top_performers');
        Schema::dropIfExists('institute_achievements');
        Schema::dropIfExists('institute_notices');
    }
};
