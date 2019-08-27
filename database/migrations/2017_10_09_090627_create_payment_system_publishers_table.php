<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentSystemPublishersTable extends Migration
{
    public function up()
    {
        Schema::create('payment_system_publisher', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->unsignedSmallInteger('payment_system_id');
            $table->unsignedInteger('publisher_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_system_publisher');
    }
}
