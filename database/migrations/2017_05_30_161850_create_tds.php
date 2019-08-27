<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTds extends Migration
{
    public function up()
    {
        Schema::create('tds', function (Blueprint $table) {
            $table->unsignedTinyInteger('id');
        });
    }

    public function down()
    {
        Schema::drop('tds');
    }
}
