<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferOfferCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_offer_category', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('offer_id')->unsigned();
            $table->integer('offer_category_id')->unsigned();

	        $table->index('offer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('offer_offer_category');
    }

}
