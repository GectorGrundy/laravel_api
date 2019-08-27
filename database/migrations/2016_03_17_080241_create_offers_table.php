<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOffersTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('offers', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->char('hash', 8);
			$table->string('title');
			$table->string('type', 32)->comment = 'product, multitarget';
			$table->tinyInteger('is_active')->unsigned();
			$table->tinyInteger('is_private')->unsigned();
			$table->smallInteger('currency_id')->unsigned();
			$table->tinyInteger('is_detect_locale')->unsigned();
			$table->text('agreement');
			$table->text('description');
			$table->decimal('today_epc', 12, 4);
			$table->decimal('yesterday_epc', 12, 4);
			$table->decimal('week_epc', 12, 4);
			$table->decimal('month_epc', 12, 4);
			$table->decimal('today_cr', 7, 4);
			$table->decimal('yesterday_cr', 7, 4);
			$table->decimal('week_cr', 7, 4);
			$table->decimal('month_cr', 7, 4);
			$table->timestamps();
			$table->softDeletes();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('offers');
	}
}
