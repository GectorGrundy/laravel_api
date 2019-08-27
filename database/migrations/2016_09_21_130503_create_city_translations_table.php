<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCityTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('city_translations', function (Blueprint $table) {
		    $table->increments('id')->unsigned();
		    $table->integer('city_id');
		    $table->integer('locale_id');
		    $table->string('content');

		    $table->index(['city_id', 'locale_id']);
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::drop('city_translations');
    }
}
