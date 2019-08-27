<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAdminIdFieldToBalanceTransactions extends Migration
{
    public function up()
    {
        Schema::table('balance_transactions', function (Blueprint $table) {
            $table->unsignedInteger('admin_id')->after('user_id');
        });
    }
}
