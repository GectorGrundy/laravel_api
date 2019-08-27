<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentRequisitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_requisites', function (Blueprint $table) {
            $table->increments('id');
	        $table->char('hash', 8);
	        $table->integer('user_id')->unigned();
            $table->unsignedSmallInteger('payment_system_id');
	        $table->smallInteger('currency_id');
            $table->text('details');
	        $table->tinyInteger('is_primary')->unsigned();
	        $table->tinyInteger('is_verified')->unsigned();
	        $table->timestamps();
            $table->softDeletes();

	        $table->index(['user_id', 'is_verified', 'currency_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('payment_requisites');
    }
}
