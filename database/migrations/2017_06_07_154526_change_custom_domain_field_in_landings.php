<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCustomDomainFieldInLandings extends Migration
{
    public function up()
    {
        Schema::table('landings', function (Blueprint $table) {
            $table->dropColumn('custom_domain');
            $table->unsignedInteger('custom_domain_id')->after('target_id');
        });
    }

    public function down()
    {
        Schema::table('landings', function (Blueprint $table) {
            $table->dropColumn('custom_domain_id');
            $table->string('custom_domain');
        });
    }
}
