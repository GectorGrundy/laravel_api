<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSessionIdFieldToTempLeads extends Migration
{
    public function up()
    {
        Schema::table('temp_leads', function (Blueprint $table) {
            $table->string('session_id', 32)->after('s_id');

            $table->index('session_id');
        });
    }

    public function down()
    {
        //
    }
}
