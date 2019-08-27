<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetGeoStatsTable extends Migration
{
    public function up()
    {
        Schema::create('target_geo_stats', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->unsignedMediumInteger('target_geo_id');
            $table->integer('currency_id');
            $table->unsignedSmallInteger('hits');
            $table->unsignedSmallInteger('flow_hosts');
            $table->unsignedSmallInteger('held_count');
            $table->decimal('onhold_payout', 12);
            $table->unsignedSmallInteger('approved_count');
            $table->decimal('leads_payout', 12);
            $table->unsignedSmallInteger('cancelled_count');
            $table->decimal('oncancel_payout', 12);
            $table->unsignedSmallInteger('trashed_count');
            $table->decimal('ontrash_payout', 12);
            $table->decimal('profit', 12);
            $table->timestamp('datetime')->nullable();
        });
    }
}
