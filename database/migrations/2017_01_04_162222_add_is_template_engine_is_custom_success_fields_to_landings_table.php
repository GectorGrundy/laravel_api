<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsTemplateEngineIsCustomSuccessFieldsToLandingsTable extends Migration
{
	public function up()
	{
		Schema::table('landings', function ($table) {
			$table->tinyInteger('is_template_engine')->unsigned()->after('is_email_on_success');
			$table->tinyInteger('is_custom_success')->unsigned()->after('is_template_engine');
		});
	}

	public function down()
	{
		Schema::table('landings', function ($table) {
			$table->dropColumn(['is_template_engine', 'is_custom_success']);
		});
	}
}
