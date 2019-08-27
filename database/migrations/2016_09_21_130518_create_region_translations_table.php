<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegionTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('region_translations', function (Blueprint $table) {
		    $table->increments('id')->unsigned();
		    $table->integer('region_id');
		    $table->integer('locale_id');
		    $table->string('content');

		    $table->index(['region_id', 'locale_id']);
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::drop('region_translations');
    }
}
