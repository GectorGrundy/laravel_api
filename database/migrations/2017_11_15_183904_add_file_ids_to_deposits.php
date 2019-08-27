<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFileIdsToDeposits extends Migration
{
    public function up()
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->unsignedInteger('invoice_file_id')->after('description');
            $table->unsignedInteger('contract_file_id')->after('invoice_file_id');
        });
    }
}
