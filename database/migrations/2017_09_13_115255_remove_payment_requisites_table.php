<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemovePaymentRequisitesTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('payment_requisites');
    }

    public function down()
    {
        //
    }
}
