<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehouseIntegrationsTable extends Migration
{
    public function up()
    {
        Schema::create('warehouse_integrations', function (Blueprint $table) {
            $table->smallIncrements('id')->unsigned();
            $table->integer('advertiser_id')->unsigned();
            $table->string('title');
            $table->char('internal_api_key', 16);
            $table->json('extra');
            $table->string('info');
            $table->timestamps();
            $table->softDeletes();

            $table->index('internal_api_key');
        });
    }

    public function down()
    {
        Schema::drop('warehouse_integrations');
    }
}
