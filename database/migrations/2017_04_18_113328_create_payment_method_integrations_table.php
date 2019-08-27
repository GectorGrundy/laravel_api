<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentMethodIntegrationsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_method_integrations', function (Blueprint $table) {
            $table->smallIncrements('id')->unsigned();
            $table->integer('advertiser_id')->unsigned();
            $table->integer('warehouse_integration_id')->unsigned();
            $table->string('title');
            $table->char('internal_api_key', 16);
            $table->boolean('is_personal_data_required');
            $table->boolean('is_production');
            $table->json('extra');
            $table->string('info');
            $table->timestamps();
            $table->softDeletes();

            $table->index('internal_api_key');
        });
    }

    public function down()
    {
        Schema::drop('payment_method_integrations');
    }
}
