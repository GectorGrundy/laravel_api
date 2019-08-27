<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetGeoRuleLeadsStat extends Migration
{
    public function up()
    {
        Schema::create('target_geo_rule_leads_stat', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('target_geo_rule_id');
            $table->unsignedInteger('leads_count');
            $table->date('date');

            $table->unique(['target_geo_rule_id', 'date']);
        });
    }

    public function down()
    {
        Schema::drop('target_geo_rule_leads_stat');

    }
}
