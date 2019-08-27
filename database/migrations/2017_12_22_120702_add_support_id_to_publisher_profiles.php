<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSupportIdToPublisherProfiles extends Migration
{
    public function up()
    {
        Schema::table('publisher_profiles', function (Blueprint $table) {
            $table->unsignedInteger('support_id')->after('user_id');
        });
    }
}
