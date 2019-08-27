<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsConfiguredFieldToDomains extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('domains', function ($table) {
			$table->tinyInteger('is_configured')->unsigned()->after('fallback_flow_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('domains', function ($table) {
			$table->dropColumn('is_configured');
		});
	}
}
