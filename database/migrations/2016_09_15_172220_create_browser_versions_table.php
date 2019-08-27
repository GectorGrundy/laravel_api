<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrowserVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('browser_versions', function (Blueprint $table) {
	        $table->smallIncrements('id')->unsigned();
	        $table->smallInteger('browser_id')->unsigned();
	        $table->string('title');

	        $table->index(['title']);
	        $table->unique(['browser_id', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('browser_versions');
    }
}
