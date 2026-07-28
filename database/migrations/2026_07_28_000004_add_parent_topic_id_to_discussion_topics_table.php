<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discussion_topics', function (Blueprint $table): void {
            $table->foreignId('parent_topic_id')
                ->nullable()
                ->after('is_group')
                ->constrained('discussion_topics')
                ->nullOnDelete();
            $table->index(['parent_topic_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('discussion_topics', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('parent_topic_id');
        });
    }
};
