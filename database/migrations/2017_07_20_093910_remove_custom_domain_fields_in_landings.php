<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveCustomDomainFieldsInLandings extends Migration
{
    public function up()
    {
        Schema::table('landings', function (Blueprint $table) {
            $table->dropColumn(['custom_domain_id', 'is_subdomain']);
        });
    }

    public function down()
    {
        Schema::table('landings', function (Blueprint $table) {
            $table->integer('custom_domain_id');
            $table->boolean('is_subdomain');
        });
    }
}
