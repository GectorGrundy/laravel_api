<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreorderVisitsTable extends Migration
{
    public function up()
    {
        Schema::create('preorder_visits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('preorder_id')->unsigned();
            $table->char('origin', 4);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('preorder_visits');
    }
}
