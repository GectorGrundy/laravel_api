<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
	public function up()
	{
		Schema::create('products', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->char('hash', 8);
			$table->integer('target_geo_id')->unsigned();
			$table->string('title');
			$table->text('description');
			$table->tinyInteger('is_active')->unsigned();
			$table->decimal('price', 12, 2);
			$table->smallInteger('currency_id')->unsigned();
			$table->char('external_key', 32);
			$table->timestamps();
			$table->softDeletes();

			$table->index('hash');
			$table->index('external_key');
		});
	}

	public function down()
	{
		Schema::drop('products');
	}
}
