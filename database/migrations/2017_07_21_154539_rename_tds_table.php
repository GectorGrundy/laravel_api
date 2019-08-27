<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTdsTable extends Migration
{
    public function up()
    {
        Schema::rename('tds', 'tds_domains');
    }

    public function down()
    {
        Schema::rename('tds_domains', 'tds');
    }
}
