<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetGeoRulesTable extends Migration
{
	public function up()
	{
		Schema::create('target_geo_rules', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->unsignedSmallInteger('integration_id');
			$table->unsignedInteger('target_geo_id');
			$table->unsignedInteger('advertiser_id');
            $table->unsignedSmallInteger('priority');
			$table->unsignedSmallInteger('limit');
            $table->unsignedTinyInteger('weight');
            $table->boolean('is_fallback');
            $table->json('integration_data');

            $table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::drop('target_geo_rules');
	}
}
