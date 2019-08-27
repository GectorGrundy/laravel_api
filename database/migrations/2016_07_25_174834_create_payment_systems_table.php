<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentSystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_systems', function (Blueprint $table) {
            $table->smallIncrements('id')->unsigned();
	        $table->string('title');
	        $table->string('status', 32)->comment = 'active, stopped';
	        $table->decimal('min_rub_payment', 12, 2);
	        $table->decimal('min_usd_payment', 12, 2);
	        $table->decimal('min_eur_payment');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('payment_systems');
    }
}
