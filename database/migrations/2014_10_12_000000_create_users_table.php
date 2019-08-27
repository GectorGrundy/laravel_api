<?php
declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id')->unsigned();
	        $table->char('hash', 8);
            $table->string('email');
	        $table->string('nickname');
            $table->string('role');
            $table->string('status', 32)->default('active');
            $table->char('locale', 2)->default('ru');
            $table->string('timezone', 32)->default('Europe/Moscow');
            $table->unsignedInteger('group_id');
            $table->string('reason_for_blocking', 512);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
	        $table->string('password');

	        $table->index('email');
	        $table->index(['role', 'status']);
        });
    }

    public function down()
    {
        Schema::drop('users');
    }
}
