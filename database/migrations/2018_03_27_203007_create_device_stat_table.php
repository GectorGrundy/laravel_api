<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeviceStatTable extends Migration
{
    public function up()
    {
        Schema::create('device_stat', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->unsignedMediumInteger('publisher_id');
            $table->unsignedMediumInteger('flow_id');
            $table->unsignedSmallInteger('offer_id');
            $table->unsignedTinyInteger('country_id');
            $table->unsignedTinyInteger('target_geo_country_id');
            $table->integer('currency_id');
            $table->smallInteger('browser_id');
            $table->smallInteger('os_platform_id');
            $table->smallInteger('device_type_id');
            $table->unsignedMediumInteger('landing_id');
            $table->unsignedMediumInteger('transit_id');
            $table->string('data1', 32);
            $table->string('data2', 32);
            $table->string('data3', 32);
            $table->string('data4', 32);
            $table->unsignedSmallInteger('hits');
            $table->unsignedSmallInteger('flow_hosts');
            $table->unsignedSmallInteger('publisher_hosts');
            $table->unsignedSmallInteger('offer_hosts');
            $table->unsignedSmallInteger('system_hosts');
            $table->unsignedSmallInteger('traffback_count');
            $table->unsignedMediumInteger('bot_count');
            $table->unsignedSmallInteger('safepage_count');
            $table->decimal('traffic_cost', 12);
            $table->unsignedSmallInteger('held_count');
            $table->decimal('onhold_payout', 12);
            $table->unsignedSmallInteger('approved_count');
            $table->decimal('leads_payout', 12);
            $table->unsignedSmallInteger('cancelled_count');
            $table->decimal('oncancel_payout', 12);
            $table->unsignedSmallInteger('trashed_count');
            $table->decimal('ontrash_payout', 12);
            $table->decimal('revshare_payout', 12);
            $table->decimal('profit', 12);
            $table->timestamp('datetime')->nullable();
        });

        Schema::table('device_stat', function (Blueprint $table) {
            $table->unique([
                'datetime', 'publisher_id', 'currency_id', 'flow_id', 'offer_id', 'country_id', 'target_geo_country_id',
                'browser_id', 'os_platform_id', 'device_type_id', 'landing_id', 'transit_id', 'data1', 'data2',
                'data3', 'data4',
            ], 'unique_index');
        });
    }
}
