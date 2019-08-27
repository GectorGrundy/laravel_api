<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTempLeadsTable extends Migration
{
    public function up()
    {
        Schema::create('temp_leads', function (Blueprint $table) {
            $table->increments('id');
            $table->char('hash', 8);
            $table->unsignedInteger('lead_id');
            $table->unsignedInteger('target_geo_id');
            $table->unsignedInteger('flow_id');
            $table->unsignedInteger('transit_id');
            $table->unsignedInteger('landing_id');
            $table->unsignedInteger('domain_id');
            $table->unsignedInteger('ip_country_id');
            $table->unsignedInteger('region_id');
            $table->unsignedInteger('city_id');
            $table->boolean('is_extra_flow');
            $table->char('browser_locale', 2);
            $table->string('name');
            $table->string('phone');
            $table->string('comment');
            $table->jsonb('products');
            $table->string('ip');
            $table->jsonb('ips');
            $table->string('data1');
            $table->string('data2');
            $table->string('data3');
            $table->string('data4');
            $table->string('clickid');
            $table->string('s_id');
            $table->string('user_agent');
            $table->string('referer');
            $table->string('transit_traffic_type');
            $table->timestamp('initialized_at')->nullable();
            $table->timestamps();

            $table->index(['flow_id', 'landing_id', 'phone', 's_id']);
            $table->index('hash');
        });
    }

    public function down()
    {
        Schema::dropIfExists('temp_leads');
    }
}
