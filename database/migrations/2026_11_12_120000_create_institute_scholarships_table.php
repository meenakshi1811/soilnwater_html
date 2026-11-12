<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Scholarships a school, college or institute offers: shown on its profile, its Scholarships page
 * and the Education Hub's scholarships page.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institute_scholarships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('scholarship_type', 30)->default('merit');
            $table->string('amount_label')->nullable();
            $table->unsignedInteger('awards_count')->nullable();
            $table->string('eligible_for')->nullable();
            $table->string('min_marks')->nullable();
            $table->string('income_limit')->nullable();
            $table->text('description')->nullable();
            $table->text('eligibility')->nullable();
            $table->text('documents')->nullable();
            $table->text('how_to_apply')->nullable();
            $table->string('apply_url', 500)->nullable();
            $table->date('application_start')->nullable();
            $table->date('application_deadline')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['institute_id', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institute_scholarships');
    }
};
