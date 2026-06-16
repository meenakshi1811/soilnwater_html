<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            $table->string('submission_ip', 45)->nullable()->after('user_id');
            $table->timestamp('content_responsibility_accepted_at')->nullable()->after('submission_ip');
            $table->timestamp('original_work_accepted_at')->nullable()->after('content_responsibility_accepted_at');
        });

        Schema::create('community_post_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40);
            $table->string('ip_address', 45)->nullable();
            $table->json('changes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('community_post_audit_logs');

        Schema::table('community_posts', function (Blueprint $table): void {
            $table->dropColumn([
                'submission_ip',
                'content_responsibility_accepted_at',
                'original_work_accepted_at',
            ]);
        });
    }
};
