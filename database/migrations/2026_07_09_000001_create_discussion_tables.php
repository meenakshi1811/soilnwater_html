<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discussion_topics', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->foreignId('pinned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('pinned_at')->nullable();
            $table->unsignedInteger('replies_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_pinned', 'created_at']);
        });

        Schema::create('discussion_replies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('discussion_topic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('discussion_replies')->nullOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['discussion_topic_id', 'created_at']);
        });

        Schema::create('discussion_reactions', function (Blueprint $table): void {
            $table->id();
            $table->morphs('reactable');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reaction', 40);
            $table->timestamps();

            $table->unique(['reactable_type', 'reactable_id', 'user_id', 'reaction'], 'discussion_reactions_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_reactions');
        Schema::dropIfExists('discussion_replies');
        Schema::dropIfExists('discussion_topics');
    }
};
