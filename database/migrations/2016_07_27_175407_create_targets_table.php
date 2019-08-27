<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetsTable extends Migration
{
	public function up()
	{
		Schema::create('targets', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->char('hash', 8);
			$table->unsignedInteger('offer_id');
			$table->unsignedSmallInteger('locale_id');
			$table->string('title');
			$table->boolean('is_active');
			$table->boolean('is_default');
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down()
	{
		Schema::drop('targets');
	}
}
