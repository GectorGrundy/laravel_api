<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDomainIdFieldToFlowTransit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('flow_transit', function ($table) {
		    $table->integer('domain_id');
	    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::table('flow_transit', function ($table) {
		    $table->dropColumn(['domain_id']);
	    });
    }
}
