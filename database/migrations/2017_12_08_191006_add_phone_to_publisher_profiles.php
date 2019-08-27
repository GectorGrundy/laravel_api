<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhoneToPublisherProfiles extends Migration
{
    public function up()
    {
        Schema::table('publisher_profiles', function (Blueprint $table) {
            $table->string('phone', 16)->after('telegram');
        });
    }
}
