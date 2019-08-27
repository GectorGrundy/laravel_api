<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmailLogsTable extends Migration
{
    public function up()
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('entity_id');
            $table->string('entity_type', 16);
            $table->longText('html');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('email_logs');
    }
}
