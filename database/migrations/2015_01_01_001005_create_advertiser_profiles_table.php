<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAdvertiserProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('advertiser_profiles', function (Blueprint $table) {
            $table->increments('id');
	        $table->integer('user_id')->unsigned();
	        $table->string('full_name');
            $table->string('skype');
            $table->decimal('balance_usd', 12, 2);
            $table->decimal('balance_rub', 12, 2);
            $table->decimal('balance_eur', 12, 2);
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
        Schema::drop('advertiser_profiles');
    }
}
