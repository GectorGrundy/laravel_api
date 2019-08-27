<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameUserUserPermissionsTable extends Migration
{
    public function up()
    {
        Schema::rename('user_user_permissions', 'user_user_permission');
    }

    public function down()
    {
        Schema::rename('user_user_permission', 'user_user_permissions');
    }
}
