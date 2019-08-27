<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlowWidgetsTable extends Migration
{
    public function up()
    {
        Schema::create('flow_widgets', function (Blueprint $table) {
            $table->smallIncrements('id')->unsigned();
            $table->string('title');
            $table->jsonb('schema');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('flow_widgets');
    }
}
