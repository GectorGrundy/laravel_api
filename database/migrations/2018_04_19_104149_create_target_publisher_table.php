<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetPublisherTable extends Migration
{
    public function up()
    {
        Schema::create('target_publisher', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('target_id');
            $table->unsignedInteger('publisher_id');
        });
    }
}
