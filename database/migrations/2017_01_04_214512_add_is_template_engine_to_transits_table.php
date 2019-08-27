<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsTemplateEngineToTransitsTable extends Migration
{
	public function up()
	{
		Schema::table('transits', function ($table) {
			$table->tinyInteger('is_template_engine')->unsigned()->after('is_advertiser_viewable');
		});
	}

	public function down()
	{
		Schema::table('transits', function ($table) {
			$table->dropColumn(['is_template_engine']);
		});
	}
}
