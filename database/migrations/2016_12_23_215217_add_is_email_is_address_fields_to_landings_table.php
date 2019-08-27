<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsEmailIsAddressFieldsToLandingsTable extends Migration
{
	public function up()
	{
		Schema::table('landings', function ($table) {
			$table->tinyInteger('is_address_on_success')->unsigned()->after('is_advertiser_viewable');
			$table->tinyInteger('is_email_on_success')->unsigned()->after('is_address_on_success');
		});
	}

	public function down()
	{
		Schema::table('landings', function ($table) {
			$table->dropColumn(['is_address_on_success', 'is_email_on_success']);
		});
	}
}
