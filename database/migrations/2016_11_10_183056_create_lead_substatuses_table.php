<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadSubstatusesTable extends Migration
{
    public function up()
    {
	    Schema::create('lead_substatuses', function (Blueprint $table) {
		    $table->smallIncrements('id')->unsigned();
            $table->string('title');
        });
    }

    public function down()
    {
	    Schema::drop('lead_substatuses');
    }
}
