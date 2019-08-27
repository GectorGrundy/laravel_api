<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFlowsTable extends Migration
{
	public function up()
	{
		Schema::create('flows', function (Blueprint $table) {
			$table->increments('id')->unsigned();
			$table->char('hash', 8);
			$table->string('title');
            $table->integer('extra_flow_id')->unsigned();
            $table->integer('publisher_id')->unsigned();
			$table->integer('offer_id')->unsigned();
			$table->integer('target_id')->unsigned();
			$table->boolean('is_detect_mobile');
			$table->boolean('is_detect_bot');
            $table->boolean('is_hide_target_list')->unsigned();
			$table->boolean('is_cpc');
			$table->decimal('cpc', 12, 2);
            $table->tinyInteger('cpc_lost')->unsigned();
            $table->smallInteger('cpc_currency_id')->unsigned();
			$table->boolean('is_noback');
			$table->boolean('is_comebacker');
			$table->boolean('is_show_requisite');
            $table->boolean('is_remember_landing');
            $table->boolean('is_remember_transit');
            $table->string('tb_url');
            $table->timestamps();
		    $table->softDeletes();
        });
	}

	public function down()
	{
		Schema::drop('flows');
	}
}
