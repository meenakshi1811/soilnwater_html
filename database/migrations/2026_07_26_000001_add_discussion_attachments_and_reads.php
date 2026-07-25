<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discussion_topics', function (Blueprint $table): void {
            $table->json('attachments')->nullable()->after('body');
        });

        Schema::table('discussion_replies', function (Blueprint $table): void {
            $table->json('attachments')->nullable()->after('body');
            $table->text('body')->nullable()->change();
        });

        Schema::create('discussion_topic_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('discussion_topic_id')->constrained()->cascadeOnDelete();
            $table->timestamp('last_read_at');
            $table->timestamps();

            $table->unique(['user_id', 'discussion_topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_topic_reads');

        Schema::table('discussion_replies', function (Blueprint $table): void {
            $table->dropColumn('attachments');
        });

        Schema::table('discussion_topics', function (Blueprint $table): void {
            $table->dropColumn('attachments');
        });
    }
};
