<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreordersTable extends Migration
{
    public function up()
    {
        Schema::create('preorders', function (Blueprint $table) {
            $table->increments('id');
            $table->char('hash', 8);
            $table->unsignedInteger('lead_id');
            $table->unsignedInteger('domain_id');
            $table->unsignedInteger('publisher_id');
            $table->unsignedInteger('flow_id');
            $table->unsignedInteger('offer_id');
            $table->unsignedInteger('landing_id');
            $table->unsignedInteger('transit_id');
            $table->unsignedInteger('locale_id');
            $table->unsignedInteger('currency_id');
            $table->unsignedInteger('preorder_reason_id');
            $table->unsignedInteger('target_geo_id');
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('region_id');
            $table->unsignedInteger('city_id');
            $table->string('email');
            $table->string('phone');
            $table->string('name');
            $table->string('age');
            $table->boolean('is_email_notified');
            $table->boolean('is_email_opened');
            $table->char('browser_locale', 2);
            $table->unsignedInteger('ip_country_id');
            $table->unsignedSmallInteger('browser_id');
            $table->unsignedSmallInteger('browser_version_id');
            $table->unsignedSmallInteger('os_platform_id');
            $table->unsignedSmallInteger('os_version_id');
            $table->unsignedSmallInteger('device_type_id');
            $table->char('s_id', 32);
            $table->string('ip', 16);
            $table->json('ips');
            $table->string('user_agent');
            $table->string('data1', 32);
            $table->string('data2', 32);
            $table->string('data3', 32);
            $table->string('data4', 32);
            $table->string('clickid');
            $table->string('referer');
            $table->boolean('is_test');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('preorders');
    }
}
