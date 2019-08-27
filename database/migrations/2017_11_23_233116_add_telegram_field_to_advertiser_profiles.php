<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTelegramFieldToAdvertiserProfiles extends Migration
{
    public function up()
    {
        Schema::table('advertiser_profiles', function (Blueprint $table) {
            $table->string('telegram')->after('skype');
        });
    }

    public function down()
    {
        Schema::table('advertiser_profiles', function (Blueprint $table) {
            //
        });
    }
}
