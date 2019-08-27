<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCloakingDomainsTable extends Migration
{
    public function up()
    {
        Schema::create('cloaking_domains', function (Blueprint $table) {
            $table->increments('id');
        });
    }

    public function down()
    {
        Schema::drop('cloaking_domains');
    }
}
