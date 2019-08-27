<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLeadStatusLogsTable extends Migration
{
	public function up()
	{
		Schema::create('lead_status_logs', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->integer('lead_id')->unsigned();
            $table->unsignedSmallInteger('integration_id');
            $table->string('integration_type', 32);
			$table->string('status', 32);
			$table->smallInteger('sub_status_id');
            $table->string('external_key');
            $table->timestamp('foreign_changed_at')->nullable();
            $table->timestamps();
		});
	}

	public function down()
	{
		Schema::drop('lead_status_logs');
	}
}
