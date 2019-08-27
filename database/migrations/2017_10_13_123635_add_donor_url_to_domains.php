<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDonorUrlToDomains extends Migration
{
    public function up()
    {
        Schema::table('domains', function (Blueprint $table) {
            $table->string('donor_url')->after('fallback_flow_id');
            $table->renameColumn('charset', 'donor_charset');
        });
    }

    public function down()
    {
        Schema::table('domains', function (Blueprint $table) {
            //
        });
    }
}
