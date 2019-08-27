<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthTokensTable extends Migration
{
    public function up()
    {
        Schema::create('auth_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token', 512);
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('admin_id');
            $table->string('ip', 16);
            $table->string('user_agent');
            $table->timestamps();
            $table->timestamp('last_activity')->nullable();

            $table->index('admin_id');
            $table->index('user_id');
            $table->unique('token');
        });
    }

    public function down()
    {
        Schema::drop('auth_tokens');
    }
}
