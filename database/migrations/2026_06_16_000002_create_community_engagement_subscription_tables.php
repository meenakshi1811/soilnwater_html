<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_post_saves', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'community_post_id']);
        });

        Schema::create('community_post_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reason');
            $table->timestamps();
        });

        Schema::create('community_category_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('content_type', 80);
            $table->string('category', 120);
            $table->timestamps();

            $table->unique(['user_id', 'content_type', 'category'], 'community_category_subscriptions_unique');
        });

        Schema::create('community_topic_follows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('topic', 120);
            $table->timestamps();

            $table->unique(['user_id', 'topic']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_topic_follows');
        Schema::dropIfExists('community_category_subscriptions');
        Schema::dropIfExists('community_post_reports');
        Schema::dropIfExists('community_post_saves');
    }
};
