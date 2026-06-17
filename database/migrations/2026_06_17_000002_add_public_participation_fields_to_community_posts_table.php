<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_posts', function (Blueprint $table): void {
            if (! Schema::hasColumn('community_posts', 'allow_suggestions')) {
                $table->boolean('allow_suggestions')->default(false)->after('allow_comments');
            }
            if (! Schema::hasColumn('community_posts', 'allow_feedback')) {
                $table->boolean('allow_feedback')->default(false)->after('allow_suggestions');
            }
            if (! Schema::hasColumn('community_posts', 'allow_additional_evidence')) {
                $table->boolean('allow_additional_evidence')->default(false)->after('allow_feedback');
            }
        });

        if (! Schema::hasTable('community_post_participations')) {
            Schema::create('community_post_participations', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('community_post_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('type', 40);
                $table->text('body');
                $table->timestamps();
                $table->index(['community_post_id', 'type', 'created_at'], 'cpp_post_type_created_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('community_post_participations');

        Schema::table('community_posts', function (Blueprint $table): void {
            $table->dropColumn(['allow_suggestions', 'allow_feedback', 'allow_additional_evidence']);
        });
    }
};
