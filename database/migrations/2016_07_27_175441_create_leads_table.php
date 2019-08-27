<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadsTable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('leads', function (Blueprint $table) {
			$table->increments('id');
            $table->unsignedInteger('origin_lead_id');
            $table->char('hash', 8);
            $table->unsignedSmallInteger('integration_id');
            $table->unsignedInteger('payment_method_integration_id');
            $table->unsignedInteger('payment_method_id');
            $table->string('status', 32)->comment = 'new, cancelled, approved, trashed';
			$table->boolean('is_hold');
            $table->boolean('is_test');
            $table->boolean('is_integrated');
			$table->boolean('is_valid_phone');
            $table->unsignedInteger('domain_id');
            $table->unsignedInteger('offer_id');
			$table->unsignedInteger('target_id');
			$table->unsignedInteger('locale_id');
			$table->unsignedInteger('target_geo_rule_id');
			$table->unsignedInteger('target_geo_id');
			$table->unsignedInteger('country_id');
			$table->unsignedInteger('region_id');
			$table->unsignedInteger('city_id');
			$table->unsignedSmallInteger('currency_id');
			$table->unsignedInteger('publisher_id');
			$table->unsignedInteger('advertiser_id');
			$table->unsignedInteger('landing_id');
			$table->unsignedInteger('transit_id');
			$table->unsignedInteger('flow_id');
			$table->unsignedInteger('order_id');
            $table->unsignedInteger('ip_country_id');
            $table->unsignedSmallInteger('browser_id');
            $table->unsignedSmallInteger('browser_version_id');
            $table->unsignedSmallInteger('os_platform_id');
            $table->unsignedSmallInteger('os_version_id');
            $table->unsignedSmallInteger('device_type_id');
            $table->string('transit_traffic_type', 16);
            $table->char('browser_locale', 2);
            $table->string('browser');
            $table->boolean('is_extra_flow');
            $table->string('ip', 16);
            $table->json('ips');
            $table->string('data1', 32);
            $table->string('data2', 32);
            $table->string('data3', 32);
            $table->string('data4', 32);
            $table->string('clickid');
            $table->char('s_id', 32);
            $table->string('user_agent');
            $table->string('referer');
			$table->string('external_key');
			$table->decimal('payout', 12);
			$table->decimal('profit', 12);
            $table->decimal('price', 12);
            $table->string('origin', 32)->comment = 'web, api';
            $table->string('type', 16)->comment = 'cod, online';
            $table->unsignedTinyInteger('sub_status_id');
            $table->string('sub_status');
            $table->unsignedSmallInteger('hold_time');
            $table->timestamp('initialized_at')->nullable();
            $table->timestamps();
            $table->timestamp('processed_at')->nullable();
            $table->softDeletes();

			$table->index(['external_key']);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('leads');
	}
}
