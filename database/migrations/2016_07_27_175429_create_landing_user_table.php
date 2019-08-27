<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLandingUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('landing_user', function (Blueprint $table) {
		    $table->increments('id')->unsigned();
		    $table->integer('publisher_id')->unsigned();
		    $table->integer('landing_id')->unsigned();

		    $table->index('publisher_id');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::drop('landing_user');
    }
}
