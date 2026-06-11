<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('community_author_follows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'author_id']);
        });

        Schema::create('community_post_reactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reaction', 40);
            $table->timestamps();
            $table->unique(['community_post_id', 'user_id', 'reaction'], 'community_post_reactions_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_post_reactions');
        Schema::dropIfExists('community_author_follows');
    }
};
