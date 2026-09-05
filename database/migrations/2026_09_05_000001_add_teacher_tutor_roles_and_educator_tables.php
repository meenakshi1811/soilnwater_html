<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('user','vendor','builder','developer','consultant','service_provider','teacher','admin','employee') NOT NULL DEFAULT 'user'");
        }

        Schema::create('educators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('teacher'); // kept for compatibility; always teacher
            $table->string('display_name');
            $table->string('slug')->unique();
            $table->string('profile_photo')->nullable();
            $table->string('professional_headline')->nullable();
            $table->string('tagline')->nullable();
            $table->string('associated_institute')->nullable();
            $table->string('institute_place_id')->nullable();
            $table->decimal('institute_latitude', 10, 7)->nullable();
            $table->decimal('institute_longitude', 10, 7)->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->text('residential_address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('whatsapp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('video_profile_url')->nullable();
            $table->string('video_profile_path')->nullable();
            $table->longText('about')->nullable();
            $table->string('teaching_method')->nullable();
            $table->json('languages')->nullable();
            $table->json('subjects')->nullable();
            $table->json('classes')->nullable();
            $table->json('boards')->nullable();
            $table->json('qualifications')->nullable();
            $table->json('experiences')->nullable();
            $table->json('achievements')->nullable();
            $table->json('certifications')->nullable();
            $table->json('availability')->nullable();
            $table->json('teaching_modes')->nullable();
            $table->json('service_area')->nullable();
            $table->json('teaching_stats')->nullable();
            $table->boolean('take_tuitions')->default(false);
            $table->json('tuition_classes')->nullable();
            $table->json('tuition_subjects')->nullable();
            $table->json('tuition_types')->nullable();
            $table->string('tuition_location')->nullable();
            $table->string('tuition_timings')->nullable();
            $table->string('tuition_charges')->nullable();
            $table->unsignedSmallInteger('years_experience')->default(0);
            $table->unsignedInteger('students_taught')->default(0);
            $table->decimal('success_rate', 5, 2)->nullable();
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_available_now')->default(false);
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('whatsapp_url')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('converted_from_user')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'status']);
            $table->index(['city', 'status']);
        });

        Schema::create('study_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educator_id')->constrained('educators')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_type', 20)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedSmallInteger('pages')->nullable();
            $table->string('material_type', 50)->default('notes');
            $table->string('category', 80)->nullable();
            $table->string('class_course')->nullable();
            $table->string('board_university')->nullable();
            $table->string('subject')->nullable();
            $table->string('topic_chapter')->nullable();
            $table->string('exam_test')->nullable();
            $table->string('language', 50)->nullable();
            $table->string('difficulty', 30)->nullable();
            $table->string('academic_year', 20)->nullable();
            $table->string('medium', 50)->nullable();
            $table->boolean('is_free')->default(true);
            $table->boolean('is_trending')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->json('tags')->nullable();
            $table->json('contents')->nullable();
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('downloads_count')->default(0);
            $table->unsignedInteger('saves_count')->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'material_type']);
            $table->index(['subject', 'class_course']);
            $table->index(['category', 'status']);
        });

        Schema::create('study_material_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('review')->nullable();
            $table->timestamps();

            $table->unique(['study_material_id', 'user_id']);
        });

        Schema::create('educator_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('student_name')->nullable();
            $table->string('student_class')->nullable();
            $table->unsignedTinyInteger('rating');
            $table->text('review')->nullable();
            $table->timestamps();

            $table->unique(['educator_id', 'user_id']);
        });

        Schema::create('educator_enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status', 30)->default('new');
            $table->timestamps();
        });

        Schema::create('educator_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['educator_id', 'user_id']);
        });

        Schema::create('study_material_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['study_material_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_material_bookmarks');
        Schema::dropIfExists('educator_followers');
        Schema::dropIfExists('educator_enquiries');
        Schema::dropIfExists('educator_reviews');
        Schema::dropIfExists('study_material_reviews');
        Schema::dropIfExists('study_materials');
        Schema::dropIfExists('educators');

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('user','vendor','builder','developer','consultant','service_provider','admin','employee') NOT NULL DEFAULT 'user'");
        }
    }
};
