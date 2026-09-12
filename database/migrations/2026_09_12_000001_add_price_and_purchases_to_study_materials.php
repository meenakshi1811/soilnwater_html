<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('study_materials', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('is_free');
        });

        Schema::create('study_material_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('study_material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_payment_submission_id')->nullable()->constrained('listing_payment_submissions')->nullOnDelete();
            $table->timestamp('granted_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'study_material_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('study_material_purchases');

        Schema::table('study_materials', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
