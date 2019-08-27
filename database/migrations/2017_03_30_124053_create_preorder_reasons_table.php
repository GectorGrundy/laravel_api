<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreorderReasonsTable extends Migration
{
    public function up()
    {
        Schema::create('preorder_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('preorder_reasons');
    }
}
