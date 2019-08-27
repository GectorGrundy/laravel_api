<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEntityIdAndFallbackFlowIdToDomains extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('domains', function ($table) {
			$table->integer('entity_id')->unsigned()->after('type');
			$table->integer('fallback_flow_id')->unsigned()->after('entity_id');
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
			$table->dropColumn(['entity_id', 'fallback_flow_id']);
		});
	}
}
