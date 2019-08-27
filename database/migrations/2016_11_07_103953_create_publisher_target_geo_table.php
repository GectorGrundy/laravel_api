<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePublisherTargetGeoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('publisher_target_geo', function (Blueprint $table) {
		    $table->increments('id')->unsigned();
		    $table->integer('target_geo_id');
		    $table->integer('publisher_id');
		    $table->decimal('payout', 12, 2);
		    $table->decimal('profit', 12, 2);
		    $table->decimal('price', 12, 2);
		    $table->decimal('old_price', 12, 2);
		    $table->smallInteger('hold_time')->unsigned();

		    $table->unique(['target_geo_id', 'publisher_id']);
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::drop('publisher_target_geo');
    }
}
