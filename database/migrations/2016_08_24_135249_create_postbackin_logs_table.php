<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostbackinLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postbackin_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('intergation_id')->comment = '1-CallNinja,2-TemporaryLead';
            $table->json('request');
            $table->string('request_ip');
            $table->timestamps();

	        $table->index('intergation_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('postbackin_logs');
    }
}
