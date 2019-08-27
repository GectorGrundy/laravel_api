<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdministratorProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('administrator_profiles', function (Blueprint $table) {
            $table->increments('id');
	        $table->integer('user_id')->unsigned();
	        $table->string('full_name');
            $table->string('skype');
            $table->string('telegram');
            $table->timestamps();

	        $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::drop('administrator_profiles');
    }
}
