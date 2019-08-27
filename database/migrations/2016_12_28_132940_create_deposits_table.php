<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepositsTable extends Migration
{
	public function up()
	{
		Schema::create('deposits', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->char('hash', 8);
			$table->integer('advertiser_id')->unsigned();
			$table->smallInteger('currency_id');
			$table->decimal('sum', 12, 2);
			$table->string('description');
			$table->timestamps();

			$table->index('hash');
			$table->index('advertiser_id');
		});
	}

	public function down()
	{
		Schema::drop('deposits');
	}
}
