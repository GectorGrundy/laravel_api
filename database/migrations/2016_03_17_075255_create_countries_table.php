<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCountriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->string('title', 64);
            $table->char('code', 3)->comment = 'ISO код страны';
            $table->string('timezone');
            $table->char('currency_id', 2);
	        $table->tinyInteger('is_active')->unsigned();
	        $table->string('first_phone', 16);
            $table->string('mask', 32);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('countries');
    }
}
