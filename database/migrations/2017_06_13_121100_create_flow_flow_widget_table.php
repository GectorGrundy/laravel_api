<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlowFlowWidgetTable extends Migration
{
    public function up()
    {
        Schema::create('flow_flow_widget', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->char('hash', 8);
            $table->unsignedInteger('flow_id');
            $table->unsignedSmallInteger('flow_widget_id');
            $table->jsonb('attributes');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('flow_flow_widget');
    }
}
