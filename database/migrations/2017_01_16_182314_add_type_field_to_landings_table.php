<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeFieldToLandingsTable extends Migration
{
	public function up()
	{
		Schema::table('landings', function ($table) {
			$table->string('type', 16)->after('is_custom_success')->comment = 'cod, online';
		});
	}

	public function down()
	{
		Schema::table('landings', function ($table) {
			$table->dropColumn(['type']);
		});
	}
}
