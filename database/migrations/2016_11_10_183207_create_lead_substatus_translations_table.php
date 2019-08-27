<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadSubstatusTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::create('lead_substatus_translations', function (Blueprint $table) {
		    $table->increments('id')->unsigned();
		    $table->integer('lead_substatus_id');
		    $table->integer('locale_id');
		    $table->string('title');

		    $table->index(['lead_substatus_id', 'locale_id']);
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::drop('lead_substatus_translations');
    }
}
