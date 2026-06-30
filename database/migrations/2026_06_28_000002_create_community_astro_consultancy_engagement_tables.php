<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('community_astro_consultancy_private_queries')) {
            Schema::create('community_astro_consultancy_private_queries', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('community_post_id');
                $table->foreignId('user_id')->nullable();
                $table->string('query_type', 80);
                $table->string('name', 160);
                $table->string('email', 160);
                $table->string('mobile', 40)->nullable();
                $table->text('message');
                $table->timestamps();
                $table->index(['community_post_id', 'created_at'], 'cac_queries_post_created_idx');
                $table->foreign('community_post_id', 'cac_queries_post_fk')
                    ->references('id')
                    ->on('community_posts')
                    ->cascadeOnDelete();
                $table->foreign('user_id', 'cac_queries_user_fk')
                    ->references('id')
                    ->on('users')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('community_astro_consultancy_private_queries');
    }
};
