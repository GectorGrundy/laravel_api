<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminIdToApiLogs extends Migration
{
    public function up()
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->unsignedInteger('admin_id')->after('user_id');
        });
    }

    public function down()
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->dropColumn('admin_id');
        });
    }
}
