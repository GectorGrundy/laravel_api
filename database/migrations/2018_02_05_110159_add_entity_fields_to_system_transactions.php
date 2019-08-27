<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEntityFieldsToSystemTransactions extends Migration
{
    public function up()
    {
        Schema::table('system_transactions', function (Blueprint $table) {
            $table->unsignedInteger('entity_id')->after('currency_id');
            $table->string('entity_type')->after('entity_id');
        });
    }
}
