<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('user_ads', function (Blueprint $table) {
        $table->unsignedBigInteger('ad_template_id')->nullable()->change();
    });
}

public function down()
{
    Schema::table('user_ads', function (Blueprint $table) {
        $table->unsignedBigInteger('ad_template_id')->nullable(false)->change();
    });
}
};
