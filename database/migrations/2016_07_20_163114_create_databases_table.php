<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDatabasesTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('databases', function (Blueprint $table) {
			$table->increments('id');
			$table->string('name');
			$table->string('host');
			$table->string('database');
			$table->string('username');
			$table->string('password');
			$table->integer('port');
			$table->string('type');
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
		Schema::drop('databases');
	}
}
