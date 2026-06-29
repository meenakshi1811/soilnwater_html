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
                $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('query_type', 80);
                $table->string('name', 160);
                $table->string('email', 160);
                $table->string('mobile', 40)->nullable();
                $table->text('message');
                $table->timestamps();
                $table->index(['community_post_id', 'created_at'], 'cac_queries_post_created_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('community_astro_consultancy_private_queries');
    }
};
