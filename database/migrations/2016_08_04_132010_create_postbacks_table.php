<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('publisher_id')->unsigned();
            $table->integer('flow_id')->unsigned();
            $table->char('hash', 8);
            $table->string('url', 512);
	        $table->tinyInteger('on_lead_add')->unsigned();
	        $table->tinyInteger('on_lead_approve')->unsigned();
	        $table->tinyInteger('on_lead_cancel')->unsigned();

	        $table->index('flow_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('postbacks');
    }
}
