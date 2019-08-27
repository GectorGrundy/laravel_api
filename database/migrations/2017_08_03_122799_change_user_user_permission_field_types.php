<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeUserUserPermissionFieldTypes extends Migration
{
    public function up()
    {
        Schema::table('user_user_permission', function (Blueprint $table) {
            $table->unsignedInteger('user_id')->change();
            $table->unsignedInteger('user_permission_id')->change();
        });
    }

    public function down()
    {
        Schema::table('user_user_permission', function (Blueprint $table) {
            $table->string('user_id')->change();
            $table->decimal('user_permission_id')->change();
        });
    }
}
