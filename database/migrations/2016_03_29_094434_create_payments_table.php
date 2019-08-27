<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payments', function (Blueprint $table) {
			$table->increments('id');
			$table->char('hash', 8);
			$table->integer('requisite_id')->unsigned();
			$table->integer('user_id')->unsigned();
			$table->string('user_role', 32)->comment = 'advertiser, publisher';
			$table->string('status', 32)->comment = 'pending, canceled, accepted, paid';
			$table->string('type', 32)->comment = 'payment';
			$table->smallInteger('currency_id');
			$table->decimal('payout', 12, 2);
			$table->string('description');
			$table->timestamps();

			$table->index('user_id');
			$table->index(['status', 'currency_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('payments');
	}
}
