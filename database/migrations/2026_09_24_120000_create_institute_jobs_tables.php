<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institute_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('department')->nullable();
            $table->string('employment_type')->default('full-time');
            $table->string('location')->nullable();
            $table->string('salary_label')->nullable();
            $table->string('experience_label')->nullable();
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->date('application_deadline')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['institute_id', 'status', 'published_at']);
        });

        Schema::create('institute_job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_job_id')->constrained('institute_jobs')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('cover_message')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('institute_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->unique(['institute_job_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institute_job_applications');
        Schema::dropIfExists('institute_jobs');
    }
};
