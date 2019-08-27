<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveUnsignedFromFieldInPublisherStatistics extends Migration
{
    public function up()
    {
        Schema::table('publisher_statistics', function (Blueprint $table) {
            // Remove unsigned from `approved_leads`
            DB::statement('ALTER TABLE `publisher_statistics` CHANGE `approved_leads` `approved_leads` MEDIUMINT(8) NOT NULL;');
        });
    }

    public function down()
    {
        Schema::table('publisher_statistics', function (Blueprint $table) {
            //
        });
    }
}
