<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferPreorderReason extends Migration
{
    public function up()
    {
        Schema::create('offer_preorder_reason', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('offer_id');
            $table->integer('preorder_reason_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('offer_preorder_reason');
    }
}
