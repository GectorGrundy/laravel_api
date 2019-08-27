<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColorFieldToFlowGroups extends Migration
{
    public function up()
    {
        Schema::table('flow_groups', function (Blueprint $table) {
            $table->char('color', 7)->after('title');
        });
    }

    public function down()
    {
        //
    }
}
