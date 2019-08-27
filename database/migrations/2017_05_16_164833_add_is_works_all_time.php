<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsWorksAllTime extends Migration
{
    public function up()
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->boolean('is_works_all_time')->default(1)->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->dropColumn('is_works_all_time');
        });
    }
}
