<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnIsActiveToAccountsTable extends Migration
{

    public function up()
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->boolean('is_active')->default(true);
        });
    }

}
