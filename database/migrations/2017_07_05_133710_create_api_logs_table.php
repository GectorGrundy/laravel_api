<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApiLogsTable extends Migration
{
    public function up()
    {
        Schema::create('api_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('request_method', 6);
            $table->string('api_method', 64);
            $table->text('request');
            $table->unsignedSmallInteger('response_code');
            $table->text('response');
            $table->string('user_agent');
            $table->string('ip', 32);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('api_logs');
    }
}
