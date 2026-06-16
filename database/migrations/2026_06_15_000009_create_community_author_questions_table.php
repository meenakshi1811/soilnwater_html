<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_author_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('asked_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('community_post_id')->nullable()->constrained('community_posts')->nullOnDelete();
            $table->text('question');
            $table->text('answer')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->timestamps();

            $table->index(['author_id', 'answered_at']);
            $table->index(['community_post_id', 'answered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_author_questions');
    }
};
