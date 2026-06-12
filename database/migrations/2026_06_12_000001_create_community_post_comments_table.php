<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_post_comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('community_post_comments')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();

            $table->index(['community_post_id', 'parent_id', 'created_at'], 'community_post_comments_thread_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_post_comments');
    }
};
