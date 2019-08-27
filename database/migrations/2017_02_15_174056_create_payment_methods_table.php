<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentMethodsTable extends Migration
{
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('template_id');
            $table->integer('target_geo_id');
            $table->integer('integration_id');
	        $table->smallInteger('currency_id');
            $table->string('type')->comment = 'online, cod';
            $table->boolean('is_active');
            $table->boolean('is_tax');
            $table->unsignedSmallInteger('priority');
	        $table->decimal('delivery_cost', 12);
            $table->decimal('processing_cost', 12);
            $table->jsonb('integration_data');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::drop('payment_methods');
    }
}
