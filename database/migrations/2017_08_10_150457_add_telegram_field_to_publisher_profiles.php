<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTelegramFieldToPublisherProfiles extends Migration
{
    public function up()
    {
        Schema::table('publisher_profiles', function (Blueprint $table) {
            $table->string('telegram')->after('skype');
        });
    }

    public function down()
    {
        Schema::table('publisher_profiles', function (Blueprint $table) {
            $table->dropColumn('telegram');
        });
    }
}
