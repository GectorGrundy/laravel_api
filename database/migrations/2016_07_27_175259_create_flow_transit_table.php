<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlowTransitTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('flow_transit', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->integer('flow_id')->unsigned();
			$table->integer('transit_id')->unsigned();
			$table->tinyInteger('is_mobile')->unsigned();

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
		Schema::drop('flow_transit');
	}
}
