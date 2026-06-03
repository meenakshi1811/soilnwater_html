<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If a previous run failed while adding the long auto-generated MySQL
        // constraint name, the table may exist without this migration being
        // recorded. Drop it so the migration can be safely retried.
        Schema::dropIfExists('service_provider_service_inquiries');

        Schema::create('service_provider_service_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_provider_id');
            $table->foreignId('service_provider_service_id');
            $table->foreignId('user_id')->nullable();
            $table->string('client_name');
            $table->string('phone_number', 30);
            $table->string('email');
            $table->string('occupation')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('question');
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->foreign('service_provider_id', 'sp_inquiries_provider_fk')
                ->references('id')
                ->on('service_providers')
                ->cascadeOnDelete();
            $table->foreign('service_provider_service_id', 'sp_inquiries_service_fk')
                ->references('id')
                ->on('service_provider_services')
                ->cascadeOnDelete();
            $table->foreign('user_id', 'sp_inquiries_user_fk')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_provider_service_inquiries');
    }
};
