<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTargetUserGroupTable extends Migration
{
    public function up()
    {
        Schema::create('target_user_group', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('target_id');
            $table->unsignedInteger('user_group_id');
        });
    }
}
