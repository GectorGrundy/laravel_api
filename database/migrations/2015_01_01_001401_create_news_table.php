<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('news', function (Blueprint $table) {
            $table->increments('id');
	        $table->char('hash', 8);
            $table->integer('type_id')->unsigned();
	        $table->integer('offer_id')->unsigned();
	        $table->text('title');
            $table->text('body');
            $table->integer('author_id')->unsigned();
            $table->timestamps();
            $table->timestamp('published_at');
            $table->softDeletes();

            $table->index('published_at');
            $table->index('type_id');
            $table->index('author_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('news');
    }
}
