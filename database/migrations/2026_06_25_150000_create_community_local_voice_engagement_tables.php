<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('community_local_voice_supports')) {
            Schema::create('community_local_voice_supports', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['community_post_id', 'user_id'], 'community_local_voice_supports_unique');
            });
        }

        if (! Schema::hasTable('community_local_voice_follows')) {
            Schema::create('community_local_voice_follows', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['community_post_id', 'user_id'], 'community_local_voice_follows_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('community_local_voice_follows');
        Schema::dropIfExists('community_local_voice_supports');
    }
};
