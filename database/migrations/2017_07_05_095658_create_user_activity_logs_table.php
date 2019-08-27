<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserActivityLogsTable extends Migration
{
    public function up()
    {
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('entity_id');
            $table->string('entity_type', 32);
            $table->string('ip', 32);
            $table->string('user_agent');
            $table->text('request');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('user_activity_logs');
    }
}
