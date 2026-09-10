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
            DB::statement("ALTER TABLE users MODIFY role ENUM('user','vendor','builder','developer','consultant','service_provider','teacher','student','admin','employee') NOT NULL DEFAULT 'user'");
        }

        Schema::create('parent_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('is_enabled')->default(false);
            $table->text('bio')->nullable();
            $table->json('languages')->nullable();
            $table->string('location')->nullable();
            $table->unsignedTinyInteger('profile_completion')->default(0);
            $table->timestamps();
        });

        Schema::create('child_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('child_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone_number', 20);
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->string('class_grade')->nullable();
            $table->string('board')->nullable();
            $table->string('school_name')->nullable();
            $table->json('subjects')->nullable();
            $table->string('profile_image')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['parent_user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_profiles');
        Schema::dropIfExists('parent_profiles');

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('user','vendor','builder','developer','consultant','service_provider','teacher','admin','employee') NOT NULL DEFAULT 'user'");
        }
    }
};
