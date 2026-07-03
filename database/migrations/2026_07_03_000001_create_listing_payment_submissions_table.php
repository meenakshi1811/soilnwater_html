<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_payment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('listing_type', 32);
            $table->unsignedBigInteger('listing_id');
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('screenshot_path');
            $table->string('transaction_reference')->nullable();
            $table->text('user_note')->nullable();
            $table->string('status', 32)->default('pending');
            $table->text('admin_note')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['listing_type', 'listing_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_payment_submissions');
    }
};
