<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGroupIdToFlows extends Migration
{
    public function up()
    {
        Schema::table('flows', function (Blueprint $table) {
            $table->unsignedInteger('group_id')->after('target_id');
        });
    }

    public function down()
    {
        Schema::table('flows', function (Blueprint $table) {
            $table->dropColumn('group_id');
        });
    }
}
