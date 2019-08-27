<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaxumRequisitesTable extends Migration
{
    public function up()
    {
        Schema::create('paxum_requisites', function (Blueprint $table) {
            $table->increments('id');
            $table->char('hash', 8);
            $table->unsignedInteger('user_id');
            $table->unsignedTinyInteger('payment_system_id');
            $table->string('email');
            $table->boolean('is_editable')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paxum_requisites');
    }
}

