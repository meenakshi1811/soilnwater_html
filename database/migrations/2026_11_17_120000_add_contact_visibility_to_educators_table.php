<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // A teacher chooses which contact details their public page shows (the enquiry form is always
    // there). On by default, as they were all shown until now; the home address is never shown.
    public function up(): void
    {
        Schema::table('educators', function (Blueprint $table): void {
            $table->boolean('show_phone')->default(true)->after('whatsapp');
            $table->boolean('show_whatsapp')->default(true)->after('show_phone');
            $table->boolean('show_email')->default(true)->after('show_whatsapp');
        });
    }

    public function down(): void
    {
        Schema::table('educators', function (Blueprint $table): void {
            $table->dropColumn(['show_phone', 'show_whatsapp', 'show_email']);
        });
    }
};
