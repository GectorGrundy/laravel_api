<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentSystemCurrencyTable extends Migration
{
    public function up()
    {
        Schema::create('currency_payment_system', function (Blueprint $table) {
            $table->unsignedTinyInteger('id');
            $table->unsignedSmallInteger('payment_system_id');
            $table->unsignedSmallInteger('currency_id');
            $table->double('min_payout');

            $table->index('payment_system_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('currency_payment_system');
    }
}
