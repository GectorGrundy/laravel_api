<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComebackerAudioTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('comebacker_audio', function (Blueprint $table) {
			$table->increments('id');
			$table->char('hash', 8);
			$table->integer('locale_id')->unsigned();
			$table->string('title');
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('comebacker_audio');
	}
}
