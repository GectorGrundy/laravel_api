<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostbackoutLogsTable extends Migration
{
	public function up()
	{
		Schema::create('postbackout_logs', function (Blueprint $table) {
			$table->increments('id')->unsigned();
            $table->unsignedInteger('lead_id');
            $table->integer('postback_id')->unsigned();
			$table->text('url');
			$table->string('status', 32)->comment = 'success, fail';
            $table->string('type', 16)->comment = 'lead_add, lead_approve, lead_cancel';
            $table->timestamps();

			$table->index('lead_id');
			$table->index('postback_id');
		});
	}

	public function down()
	{
		Schema::drop('postbackout_logs');
	}
}
