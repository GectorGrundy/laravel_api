<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveJobsFieldInIntegrations extends Migration
{
    public function up()
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->dropColumn(['jobs']);
        });
    }

    public function down()
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->jsonb('jobs');
        });
    }
}
