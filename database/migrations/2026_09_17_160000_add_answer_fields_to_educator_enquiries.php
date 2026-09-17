<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('educator_enquiries', function (Blueprint $table): void {
            $table->text('answer')->nullable()->after('message');
            $table->timestamp('answered_at')->nullable()->after('answer');
        });
    }

    public function down(): void
    {
        Schema::table('educator_enquiries', function (Blueprint $table): void {
            $table->dropColumn(['answer', 'answered_at']);
        });
    }
};
