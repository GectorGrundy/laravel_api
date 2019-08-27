<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServersTable extends Migration
{
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 32);
            $table->string('user', 32);
            $table->string('host', 16);
            $table->boolean('is_active');
            $table->string('type', 32);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('servers');
    }
}
