<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClicksTable extends Migration
{
    public function up()
    {
        Schema::create('clicks', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->char('hash', 8)->unique();

            $table->unsignedInteger('target_geo_id');
            $table->unsignedInteger('domain_id');
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('region_id');
            $table->unsignedInteger('city_id');
            $table->unsignedInteger('transit_id');
            $table->unsignedInteger('ip_country_id');
            $table->unsignedInteger('landing_id');
            $table->unsignedInteger('flow_id');

            $table->char('browser_locale', 2);
            $table->string('browser');
            $table->unsignedSmallInteger('browser_id');
            $table->unsignedSmallInteger('browser_version_id');
            $table->unsignedSmallInteger('os_platform_id');
            $table->unsignedSmallInteger('os_version_id');
            $table->unsignedSmallInteger('device_type_id');

            $table->boolean('is_extra_flow');
            $table->string('ip', 16);
            $table->json('ips');
            $table->char('s_id', 32);
            $table->string('user_agent');
            $table->string('referer');

            $table->string('data1');
            $table->string('data2');
            $table->string('data3');
            $table->string('data4');
            $table->string('clickid');
            $table->string('transit_traffic_type', 16);

            $table->timestamps();
            $table->timestamp('initialized_at')->nullable();
        });
    }
}
