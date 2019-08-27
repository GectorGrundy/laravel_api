<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemTransactionsTable extends Migration
{
    public function up()
    {
        Schema::create('system_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->char('hash', 8);
            $table->unsignedInteger('user_id');
            $table->string('user_role');
            $table->string('type');
            $table->decimal('sum', 12);
            $table->unsignedSmallInteger('currency_id');
            $table->string('description');
            $table->timestamp('profit_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_transactions');
    }
}
