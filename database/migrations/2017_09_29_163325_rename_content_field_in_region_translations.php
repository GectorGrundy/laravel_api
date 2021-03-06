<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameContentFieldInRegionTranslations extends Migration
{
    public function up()
    {
        Schema::table('region_translations', function (Blueprint $table) {
            $table->renameColumn('content', 'title');
        });
    }

    public function down()
    {
        //
    }
}
