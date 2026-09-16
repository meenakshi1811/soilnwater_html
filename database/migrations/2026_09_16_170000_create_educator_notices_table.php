<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('educator_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('educator_id')->constrained('educators')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('message');
            $table->date('expires_at');
            $table->timestamps();

            $table->index(['educator_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('educator_notices');
    }
};
