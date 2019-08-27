<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriorityFieldToProductsTable extends Migration
{
	public function up()
	{
		Schema::table('products', function ($table) {
			$table->smallInteger('priority')->unsigned()->after('currency_id');
		});
	}

	public function down()
	{
		Schema::table('products', function ($table) {
			$table->dropColumn(['priority']);
		});
	}
}
