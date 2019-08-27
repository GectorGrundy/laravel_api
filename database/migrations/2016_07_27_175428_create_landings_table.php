<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLandingsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('landings', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->char('hash', 8);
			$table->smallInteger('locale_id')->unsigned();
			$table->string('title');
			$table->string('subdomain');
			$table->string('custom_domain');
			$table->integer('offer_id')->unsigned();
			$table->integer('target_id')->unsigned();
			$table->tinyInteger('is_private')->unsigned();
			$table->tinyInteger('is_mobile')->unsigned();
			$table->tinyInteger('is_active')->unsigned();
			$table->tinyInteger('is_responsive')->unsigned();
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
		Schema::drop('landings');
	}
}
