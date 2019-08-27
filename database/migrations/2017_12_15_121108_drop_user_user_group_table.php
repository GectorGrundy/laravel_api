<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropUserUserGroupTable extends Migration
{
    public function up()
    {
        Schema::drop('user_user_group');

    }
}
