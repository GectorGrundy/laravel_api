<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBalanceTransactionsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('balance_transactions', function (Blueprint $table) {
			$table->increments('id');
			$table->char('hash', 8);
			$table->integer('user_id')->unsigned();
			$table->smallInteger('currency_id');
			$table->string('type', 32);
			$table->decimal('sum', 12, 2);
			$table->string('description', 512);
			$table->timestamps();

			$table->index(['user_id', 'currency_id', 'type']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('balance_transactions');
	}
}
