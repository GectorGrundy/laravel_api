<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeletesToTempLeads extends Migration
{
    public function up()
    {
        Schema::table('temp_leads', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        //
    }
}
