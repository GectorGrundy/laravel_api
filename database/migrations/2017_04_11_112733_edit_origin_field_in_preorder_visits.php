<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditOriginFieldInPreorderVisits extends Migration
{
    public function up()
    {
        Schema::table('preorder_visits', function (Blueprint $table) {
            $table->string('origin', 32)->change();
        });
    }

    public function down()
    {
        //
    }
}
