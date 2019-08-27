<?php

use Illuminate\Database\Migrations\Migration;

class AddAttributesFieldToProductsTable extends Migration
{
	public function up()
	{
		Schema::table('products', function ($table) {
			$table->jsonb('attributes')->after('currency_id');
		});
	}

	public function down()
	{
		Schema::table('products', function ($table) {
			$table->dropColumn(['attributes']);
		});
	}
}
