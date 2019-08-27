<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePublisherProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('publisher_profiles', function (Blueprint $table) {
            $table->increments('id');
	        $table->integer('user_id')->unsigned();
	        $table->string('full_name');
            $table->string('skype');
            $table->smallInteger('tl')->unsigned();
	        $table->decimal('balance_usd', 12, 2);
	        $table->decimal('balance_rub', 12, 2);
	        $table->decimal('balance_eur', 12, 2);
	        $table->decimal('hold_usd', 12, 2);
	        $table->decimal('hold_rub', 12, 2);
	        $table->decimal('hold_eur', 12, 2);
            $table->timestamps();

	        $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('publisher_profiles');
    }
}
