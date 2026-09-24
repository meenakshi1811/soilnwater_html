<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educator_institute_affiliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educator_id')->constrained()->cascadeOnDelete();
            $table->foreignId('institute_id')->constrained()->cascadeOnDelete();
            $table->string('role_title')->nullable();
            $table->string('subject')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['educator_id', 'institute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educator_institute_affiliations');
    }
};
