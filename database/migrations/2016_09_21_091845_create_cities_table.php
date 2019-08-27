<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCitiesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cities', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->integer('country_id')->unsigned();
			$table->integer('region_id')->unsigned();
			$table->integer('geoname_id')->unsigned();
			$table->string('title');

			$table->unique(['geoname_id']);
			$table->index(['country_id', 'region_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cities');
	}
}
