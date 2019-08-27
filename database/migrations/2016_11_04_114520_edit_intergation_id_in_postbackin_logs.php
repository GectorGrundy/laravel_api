<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditIntergationIdInPostbackinLogs extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement("ALTER TABLE `postbackin_logs` CHANGE `intergation_id` `integration_id` TINYINT(4) NOT NULL;");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement("ALTER TABLE `postbackin_logs` CHANGE `integration_id` `intergation_id` TINYINT(4) NOT NULL;");
	}
}
