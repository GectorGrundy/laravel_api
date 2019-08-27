<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCustomDomainFieldInTransits extends Migration
{
    public function up()
    {
        Schema::table('transits', function (Blueprint $table) {
            $table->dropColumn('custom_domain');
            $table->unsignedInteger('custom_domain_id')->after('target_id');
        });
    }

    public function down()
    {
        //
    }
}
