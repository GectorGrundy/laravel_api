<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountryTranslationsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('country_translations', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->integer('country_id');
			$table->integer('locale_id');
			$table->string('title');

			$table->index(['country_id', 'locale_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('country_translations');
	}
}
