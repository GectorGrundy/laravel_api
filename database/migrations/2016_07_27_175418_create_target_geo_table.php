<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetGeoTable extends Migration
{
	public function up()
	{
		Schema::create('target_geo', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->char('hash', 8);
            $table->unsignedInteger('offer_id');
            $table->unsignedInteger('target_id');
			$table->unsignedInteger('country_id');
			$table->smallInteger('payout_currency_id');
			$table->smallInteger('price_currency_id');
            $table->unsignedTinyInteger('is_default');
            $table->boolean('is_percentage');
            $table->boolean('is_active');
            $table->string('target_geo_rule_sort_type')->default('priority');
            $table->decimal('payout', 12);
			$table->decimal('profit', 12);
			$table->decimal('price', 12);
			$table->decimal('old_price', 12);
			$table->unsignedSmallInteger('hold_time');
            $table->boolean('is_cpa_convertible');
            $table->decimal('cpa_payout', 12);
            $table->decimal('cpa_profit', 12);
            $table->boolean('has_regions');
            $table->softDeletes();

			$table->index('target_id');
			$table->index('country_id');
			$table->index('payout_currency_id');
			$table->index('price_currency_id');
		});
	}

	public function down()
	{
		Schema::drop('target_geo');
	}
}
