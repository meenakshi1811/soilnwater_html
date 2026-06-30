<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('premium_payment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('profile_type', 32);
            $table->unsignedBigInteger('profile_id');
            $table->string('screenshot_path');
            $table->string('transaction_reference')->nullable();
            $table->text('user_note')->nullable();
            $table->string('status', 32)->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['profile_type', 'profile_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('premium_payment_submissions');
    }
};
