<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsFreeDeliveryToProductsTable extends Migration
{
	public function up()
	{
		Schema::table('products', function ($table) {
			$table->tinyInteger('is_free_delivery')->unsigned()->after('priority');
		});
	}

	public function down()
	{
		Schema::table('products', function ($table) {
			$table->dropColumn('is_free_delivery');
		});
	}
}
