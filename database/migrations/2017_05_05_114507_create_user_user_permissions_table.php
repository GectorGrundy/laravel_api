<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserUserPermissionsTable extends Migration
{
    public function up()
    {
        Schema::create('user_user_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id');
            $table->string('user_permission_id');
            $table->timestamps();

            $table->unique(['user_id', 'user_permission_id']);
        });
    }

    public function down()
    {
        Schema::drop('user_user_permissions');

    }
}
