<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIpUserAgentFieldsToPreorderVisits extends Migration
{
    public function up()
    {
        Schema::table('preorder_visits', function (Blueprint $table) {
            $table->string('ip', 16)->after('origin');
            $table->string('user_agent')->after('ip');
        });
    }

    public function down()
    {
        Schema::table('preorder_visits', function (Blueprint $table) {
            $table->dropColumn(['ip', 'user_agent']);
        });
    }
}
