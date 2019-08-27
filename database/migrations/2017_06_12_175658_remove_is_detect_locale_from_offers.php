<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveIsDetectLocaleFromOffers extends Migration
{
    public function up()
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('is_detect_locale');
        });
    }

    public function down()
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->boolean('is_detect_locale');
        });
    }
}
