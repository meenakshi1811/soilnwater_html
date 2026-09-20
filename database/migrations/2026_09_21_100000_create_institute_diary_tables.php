<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institute_diary_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('holiday_type', 40)->default('general');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->string('academic_year', 20);
            $table->boolean('is_recurring')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['institute_id', 'academic_year', 'start_date']);
        });

        Schema::create('institute_leave_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('leave_type');
            $table->unsignedSmallInteger('allowed_days')->default(0);
            $table->json('applicable_to')->nullable();
            $table->boolean('is_paid')->default(true);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['institute_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institute_leave_rules');
        Schema::dropIfExists('institute_diary_holidays');
    }
};
