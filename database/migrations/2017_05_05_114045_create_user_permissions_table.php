<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserPermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('toggle_type');
            $table->timestamps();

            $table->index(['title']);
        });
    }

    public function down()
    {
        Schema::drop('user_permissions');
    }
}
