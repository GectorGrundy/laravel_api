<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOsVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('os_versions', function (Blueprint $table) {
	        $table->smallIncrements('id')->unsigned();
	        $table->smallInteger('os_platform_id')->unsigned();
	        $table->string('title');

	        $table->index('title');
	        $table->unique(['os_platform_id', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('os_versions');
    }
}
