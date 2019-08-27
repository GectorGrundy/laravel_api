<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveIsConfiguredFieldInDomains extends Migration
{
    public function up()
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn('is_configured');
        });
    }

    public function down()
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->boolean('is_configured');
        });
    }
}
