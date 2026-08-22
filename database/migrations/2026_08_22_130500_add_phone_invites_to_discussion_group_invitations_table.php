<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discussion_group_invitations', function (Blueprint $table): void {
            $table->dropForeign(['invitee_id']);
        });

        Schema::table('discussion_group_invitations', function (Blueprint $table): void {
            $table->unsignedBigInteger('invitee_id')->nullable()->change();
            $table->string('invitee_phone', 20)->nullable()->after('invitee_id');
            $table->string('token', 64)->nullable()->unique()->after('invitee_phone');

            $table->foreign('invitee_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index(['discussion_topic_id', 'invitee_phone', 'status'], 'dgi_topic_phone_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('discussion_group_invitations', function (Blueprint $table): void {
            $table->dropIndex('dgi_topic_phone_status_idx');
            $table->dropForeign(['invitee_id']);
            $table->dropColumn(['invitee_phone', 'token']);
        });

        Schema::table('discussion_group_invitations', function (Blueprint $table): void {
            $table->unsignedBigInteger('invitee_id')->nullable(false)->change();
            $table->foreign('invitee_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
