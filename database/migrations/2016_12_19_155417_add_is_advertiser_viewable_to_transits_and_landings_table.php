<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsAdvertiserViewableToTransitsAndLandingsTable extends Migration
{

	public function up()
	{
		Schema::table('transits', function ($table) {
			$table->tinyInteger('is_advertiser_viewable')->unsigned()->after('is_responsive');
		});
		Schema::table('landings', function ($table) {
			$table->tinyInteger('is_advertiser_viewable')->unsigned()->after('is_responsive');
		});
	}


	public function down()
	{
		Schema::table('transits', function ($table) {
			$table->dropColumn('is_advertiser_viewable');
		});
		Schema::table('landings', function ($table) {
			$table->dropColumn('is_advertiser_viewable');
		});
	}
}
