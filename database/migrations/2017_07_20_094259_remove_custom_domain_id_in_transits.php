<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveCustomDomainIdInTransits extends Migration
{
    public function up()
    {
        Schema::table('transits', function (Blueprint $table) {
            $table->dropColumn(['custom_domain_id']);
        });
    }

    public function down()
    {
        Schema::table('transits', function (Blueprint $table) {
            $table->integer('custom_domain_id');
        });
    }
}
