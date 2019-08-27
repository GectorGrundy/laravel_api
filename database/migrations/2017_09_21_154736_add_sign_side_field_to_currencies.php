<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSignSideFieldToCurrencies extends Migration
{
    public function up()
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->string('sign_side')->after('sign')->default('right');
        });
    }

    public function down()
    {
        //
    }
}
