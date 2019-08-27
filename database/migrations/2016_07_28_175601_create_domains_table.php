<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDomainsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('domains', function (Blueprint $table) {
			$table->increments('id');
			$table->char('hash', 8);
			$table->string('domain');
			$table->integer('user_id')->unsigned();
			$table->string('type', 32)->comment = 'tds, transit, landing';
			$table->timestamps();
			$table->softDeletes();

			$table->index('user_id');
			$table->index('type');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('domains');
	}
}
