<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCloakSystemsTable extends Migration
{
    public function up()
    {
        Schema::create('cloak_systems', function (Blueprint $table) {
            $table->smallIncrements('id')->unsigned();
            $table->string('title');
            $table->jsonb('schema');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('cloak_systems');
    }
}
