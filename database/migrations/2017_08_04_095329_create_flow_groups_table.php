<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlowGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('flow_groups', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->char('hash', 8);
            $table->unsignedInteger('publisher_id');
            $table->string('title');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('flow_groups');
    }
}
