<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeliveryCostToProductsTable extends Migration
{
	public function up()
	{
		Schema::table('products', function ($table) {
			$table->decimal('delivery_cost', 12, 2)->after('price');
		});
	}

	public function down()
	{
		Schema::table('products', function ($table) {
			$table->dropColumn(['delivery_cost']);
		});
	}
}
