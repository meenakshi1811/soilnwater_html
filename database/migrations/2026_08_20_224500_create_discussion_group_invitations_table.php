<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('discussion_group_invitations');

        Schema::create('discussion_group_invitations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('discussion_topic_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inviter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('invitee_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['invitee_id', 'status'], 'dgi_invitee_status_idx');
            $table->index(['discussion_topic_id', 'invitee_id', 'status'], 'dgi_topic_invitee_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discussion_group_invitations');
    }
};
