<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransitsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transits', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->char('hash', 8);
			$table->smallInteger('locale_id')->unsigned();
			$table->string('title');
			$table->string('subdomain');
			$table->string('custom_domain');
			$table->integer('offer_id');
			$table->integer('target_id');
			$table->tinyInteger('is_private')->unsigned();
			$table->tinyInteger('is_mobile')->unsigned();
			$table->tinyInteger('is_active')->unsigned();
			$table->tinyInteger('is_responsive')->unsigned();
			$table->decimal('today_ctr', 5, 2);
			$table->decimal('yesterday_ctr', 5, 2);
			$table->decimal('week_ctr', 5, 2);
			$table->decimal('month_ctr', 5, 2);
			$table->timestamps();
			$table->softDeletes();

			$table->index('subdomain');
			$table->index(['offer_id', 'target_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('transits');
	}
}
