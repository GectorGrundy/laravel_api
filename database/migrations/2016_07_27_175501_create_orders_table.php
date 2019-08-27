<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
	public function up()
	{
		Schema::create('orders', function (Blueprint $table) {
			$table->increments('id')->unsigned();
            $table->char('hash', 8);
            $table->unsignedInteger('country_id');
            $table->unsignedInteger('target_geo_region_id');
			$table->string('name');
            $table->string('last_name');
            $table->string('full_name');
            $table->string('phone');
			$table->jsonb('info');
            $table->string('address');
            $table->string('email');
            $table->string('street');
            $table->string('house', 16);
            $table->string('apartment', 16);
            $table->string('zipcode', 16);
            $table->string('city', 32);
            $table->json('products');
            $table->string('comment');
            $table->boolean('is_corrected');
            $table->boolean('is_first_email_notified');
            $table->boolean('is_first_email_opened');
            $table->boolean('is_tracking_number_sms_notified');
            $table->unsignedTinyInteger('number_type_id');
            $table->decimal('product_cost', 12, 2);
            $table->string('product_cost_sign', 8);
            $table->decimal('delivery_cost', 12, 2);
            $table->string('delivery_cost_sign', 8);
            $table->decimal('tax_cost', 12, 2);
            $table->string('tax_cost_sign', 8);
            $table->decimal('total_cost', 12, 2);
            $table->string('total_cost_sign', 8);
            $table->string('document');
            $table->json('history');
            $table->char('payment_external_key', 32);
            $table->text('payment_checkout_url');
            $table->string('tracking_number', 64);
            $table->timestamp('tracked_at')->nullable();
            $table->unsignedSmallInteger('integration_id');
            $table->char('integration_external_key', 32);
            $table->timestamp('integrated_at')->nullable();
            $table->timestamps();

            $table->index('payment_external_key');
            $table->index('integration_external_key');
        });
	}

	public function down()
	{
		Schema::drop('orders');
	}
}
