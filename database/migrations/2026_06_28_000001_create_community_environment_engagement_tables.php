<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('community_environment_supports')) {
            Schema::create('community_environment_supports', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['community_post_id', 'user_id'], 'community_environment_supports_unique');
            });
        }

        if (! Schema::hasTable('community_environment_follows')) {
            Schema::create('community_environment_follows', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['community_post_id', 'user_id'], 'community_environment_follows_unique');
            });
        }

        if (! Schema::hasTable('community_environment_volunteers')) {
            Schema::create('community_environment_volunteers', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name', 160);
                $table->string('mobile', 40);
                $table->string('email', 160)->nullable();
                $table->string('city', 120)->nullable();
                $table->string('interest', 80)->nullable();
                $table->timestamps();
                $table->index(['community_post_id', 'created_at'], 'ce_volunteers_post_created_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('community_environment_volunteers');
        Schema::dropIfExists('community_environment_follows');
        Schema::dropIfExists('community_environment_supports');
    }
};
