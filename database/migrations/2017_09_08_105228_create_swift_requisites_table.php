<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSwiftRequisitesTable extends Migration
{
    public function up()
    {
        Schema::create('swift_requisites', function (Blueprint $table) {
            $table->increments('id');
            $table->char('hash', 8);
            $table->unsignedInteger('user_id');
            $table->unsignedTinyInteger('payment_system_id');
            $table->char('card_number', 16);
            $table->char('expires', 16);
            $table->date('birthday');
            $table->string('document');
            $table->string('country');
            $table->string('street');
            $table->string('card_holder');
            $table->string('phone');
            $table->string('tax_id');
            $table->boolean('is_editable')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('swift_requisites');
    }
}
