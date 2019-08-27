<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMyOfferTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('my_offers', function (Blueprint $table) {
		    $table->increments('id');
		    $table->integer('publisher_id');
		    $table->integer('offer_id');

		    $table->index(['publisher_id', 'offer_id']);
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::drop('my_offers');

    }
}
