<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsSubdomainToDomains extends Migration
{
    public function up()
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->boolean('is_subdomain')->after('realpath');
        });
    }

    public function down()
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->dropColumn('is_subdomain');
        });
    }
}
