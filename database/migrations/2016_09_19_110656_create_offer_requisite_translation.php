<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferRequisiteTranslation extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('offer_requisite_translation', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->integer('offer_id');
			$table->integer('locale_id');
			$table->text('content');

			$table->index(['offer_id', 'locale_id']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('offer_requisite_translation');
	}
}
