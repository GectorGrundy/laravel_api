<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetGeoRegionsTable extends Migration
{
    public function up()
    {
        Schema::create('target_geo_regions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('country_id')->unsigned();
            $table->string('title');
            $table->decimal('tax', 12);
            $table->tinyInteger('tax_percent');

            $table->index(['country_id']);
        });
    }

    public function down()
    {
        Schema::drop('target_geo_regions');
    }
}
