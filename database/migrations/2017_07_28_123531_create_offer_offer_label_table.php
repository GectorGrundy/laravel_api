<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferOfferLabelTable extends Migration
{
    public function up()
    {
        Schema::create('offer_offer_label', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->unsignedInteger('offer_id');
            $table->unsignedInteger('offer_label_id');
        });
    }

    public function down()
    {
        Schema::drop('offer_offer_label');
    }
}
