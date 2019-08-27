<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommentAndRemoveIsApiEnabledInPublisherProfiles extends Migration
{
    public function up()
    {
        Schema::table('publisher_profiles', function (Blueprint $table) {
            $table->dropColumn(['is_api_enabled']);
            $table->string('comment')->after('phone');
        });
    }
}
