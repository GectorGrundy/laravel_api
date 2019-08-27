<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsCacheResultToCloakDomainPaths extends Migration
{
    public function up()
    {
        Schema::table('cloak_domain_paths', function (Blueprint $table) {
            $table->boolean('is_cache_result')->after('status');
        });
    }

    public function down()
    {
        //
    }
}
