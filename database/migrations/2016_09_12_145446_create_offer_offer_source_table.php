<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferOfferSourceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('offer_offer_source', function (Blueprint $table) {
		    $table->increments('id')->unsigned();
		    $table->integer('offer_id');
		    $table->integer('offer_source_id');

		    $table->index(['offer_id', 'offer_source_id']);
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::drop('offer_offer_source');

    }
}
