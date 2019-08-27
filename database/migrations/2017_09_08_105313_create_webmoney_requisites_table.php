<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWebmoneyRequisitesTable extends Migration
{
    public function up()
    {
        Schema::create('webmoney_requisites', function (Blueprint $table) {
            $table->increments('id');
            $table->char('hash', 8);
            $table->unsignedInteger('user_id');
            $table->unsignedTinyInteger('payment_system_id');
            $table->char('purse', 13);
            $table->boolean('is_editable')->default(1);
            $table->timestamps();
            $table->softDeletes();

            $table->index('purse');
        });
    }

    public function down()
    {
        Schema::dropIfExists('webmoney_requisites');
    }
}
