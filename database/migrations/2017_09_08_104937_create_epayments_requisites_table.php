<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEpaymentsRequisitesTable extends Migration
{
    public function up()
    {
        Schema::create('epayments_requisites', function (Blueprint $table) {
            $table->increments('id');
            $table->char('hash', 8);
            $table->unsignedInteger('user_id');
            $table->unsignedTinyInteger('payment_system_id');
            $table->char('ewallet', 10);
            $table->boolean('is_editable')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('epayments_requisites');
    }
}
