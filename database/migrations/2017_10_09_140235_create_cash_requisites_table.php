<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCashRequisitesTable extends Migration
{
    public function up()
    {
        Schema::create('cash_requisites', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedSmallInteger('payment_system_id');
            $table->char('hash', 8);
            $table->char('title', 4);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cash_requisites');
    }
}
