<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeFieldToEmailLogs extends Migration
{
    public function up()
    {
        Schema::table('email_logs', function (Blueprint $table) {
            $table->string('type')->after('entity_type');
        });
    }


    public function down()
    {
        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropColumn(['type']);
        });
    }
}
