<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentMethodTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('payment_method_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('locale_id');
	        $table->string('title');
	        $table->text('description');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('payment_method_templates');
    }
}
