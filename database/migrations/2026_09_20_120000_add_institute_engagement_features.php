<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('institutes', function (Blueprint $table) {
            $table->string('brochure_path')->nullable()->after('website_url');
        });

        Schema::create('institute_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['institute_id', 'user_id']);
        });

        Schema::create('institute_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['institute_id', 'user_id']);
        });

        Schema::create('institute_compare_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'institute_id']);
        });

        Schema::create('institute_engagements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 40);
            $table->timestamps();

            $table->index(['institute_id', 'action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institute_engagements');
        Schema::dropIfExists('institute_compare_items');
        Schema::dropIfExists('institute_bookmarks');
        Schema::dropIfExists('institute_followers');

        Schema::table('institutes', function (Blueprint $table) {
            $table->dropColumn('brochure_path');
        });
    }
};
