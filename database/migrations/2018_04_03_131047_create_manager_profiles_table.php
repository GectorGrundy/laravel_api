<?php
declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateManagerProfilesTable extends Migration
{
    public function up()
    {
        Schema::create('manager_profiles', function (Blueprint $table) {
            $table->mediumIncrements('id')->unsigned();
            $table->unsignedInteger('user_id');
            $table->string('full_name');
            $table->string('skype');
            $table->string('telegram');
            $table->string('phone');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('manager_profiles');
    }
}
