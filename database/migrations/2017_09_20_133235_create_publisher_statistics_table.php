<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePublisherStatisticsTable extends Migration
{
    public function up()
    {
        Schema::create('publisher_statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('publisher_id');
            $table->unsignedInteger('flow_id');
            $table->unsignedSmallInteger('currency_id');
            $table->unsignedMediumInteger('hosts');
            $table->decimal('payout', 12);
            $table->unsignedMediumInteger('leads');
            $table->unsignedMediumInteger('approved_leads');
            $table->timestamp('datetime')->nullable();

            $table->unique(['publisher_id', 'flow_id', 'currency_id', 'datetime'], 'unique_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('publisher_statistics');
    }
}
