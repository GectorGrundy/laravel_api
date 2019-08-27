<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCcIntegrationWorkTimeTable extends Migration
{
    public function up()
    {
        Schema::create('cc_integration_work_time', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('integration_id');
            $table->tinyInteger('day')->comment = "date('N') - от 1 до 7";
            $table->tinyInteger('hour')->comment = "date('G') - от 0 до 23";
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('cc_integration_work_time');
    }
}
