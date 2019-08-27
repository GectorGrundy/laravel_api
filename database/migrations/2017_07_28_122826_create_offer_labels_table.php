<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferLabelsTable extends Migration
{
    public function up()
    {
        Schema::create('offer_labels', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 32);
            $table->char('color', 7);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('offer_labels');
    }
}
