<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_stats', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer('publisher_id')->unsigned();
            $table->string('title');
	        $table->string('type', 32)->comment = 'data1, data2, data3, data4';
            $table->bigInteger('hits')->unsigned();

	        $table->unique(['publisher_id', 'title', 'type'], 'publisher_id_title_type_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('data_stats');
    }
}
