<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferCountryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('offer_country', function (Blueprint $table) {
		    $table->increments('id');
		    $table->integer('offer_id');
		    $table->integer('country_id');

		    $table->index(['offer_id', 'country_id']);
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::drop('offer_country');
    }
}
