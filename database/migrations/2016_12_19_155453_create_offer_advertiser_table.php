<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferAdvertiserTable extends Migration
{

	public function up()
	{
		Schema::create('offer_advertiser', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->integer('offer_id')->unsigned();
			$table->integer('advertiser_id')->unsigned();

			$table->index('offer_id');
			$table->index('advertiser_id');
		});
	}


	public function down()
	{
		Schema::drop('offer_advertiser');
	}
}
