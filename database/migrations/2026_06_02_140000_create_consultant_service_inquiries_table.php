<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultant_service_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consultant_service_id')->constrained('consultant_services')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('client_name');
            $table->string('phone_number', 30);
            $table->string('email');
            $table->string('occupation')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('question');
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultant_service_inquiries');
    }
};
