<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSubdomainToLandings extends Migration
{
    public function up()
    {
	    Schema::table('landings', function (Blueprint $table) {
		    $table->boolean('is_subdomain')->after('custom_domain');
	    });
    }

    public function down()
    {
	    Schema::table('landings', function (Blueprint $table) {
		    $table->dropColumn('is_subdomain');
	    });
    }
}
