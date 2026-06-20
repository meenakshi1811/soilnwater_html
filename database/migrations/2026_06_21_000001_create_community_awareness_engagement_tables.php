<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('community_awareness_supports')) {
            Schema::create('community_awareness_supports', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['community_post_id', 'user_id'], 'community_awareness_supports_unique');
            });
        }

        if (! Schema::hasTable('community_awareness_pledges')) {
            Schema::create('community_awareness_pledges', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('pledge_text', 255);
                $table->timestamps();
                $table->unique(['community_post_id', 'user_id'], 'community_awareness_pledges_unique');
            });
        }

        if (! Schema::hasTable('community_awareness_volunteers')) {
            Schema::create('community_awareness_volunteers', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('name', 160);
                $table->string('mobile', 40);
                $table->string('email', 160)->nullable();
                $table->string('city', 120)->nullable();
                $table->timestamps();
                $table->index(['community_post_id', 'created_at'], 'ca_volunteers_post_created_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('community_awareness_volunteers');
        Schema::dropIfExists('community_awareness_pledges');
        Schema::dropIfExists('community_awareness_supports');
    }
};
