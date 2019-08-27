<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGeoFieldsToPreorderVisits extends Migration
{
    public function up()
    {
        Schema::table('preorder_visits', function (Blueprint $table) {
            $table->integer('country_id')->unsigned()->after('ip');
            $table->integer('region_id')->unsigned()->after('country_id');
            $table->integer('city_id')->unsigned()->after('region_id');
        });
    }

    public function down()
    {
        Schema::table('preorder_visits', function (Blueprint $table) {
            $table->dropColumn(['country_id', 'region_id', 'city_id']);
        });
    }
}
