<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSchemaToIntegrations extends Migration
{
    public function up()
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->jsonb('schema')->after('is_works_all_time');
        });
    }

    public function down()
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->dropColumn('schema');
        });
    }
}
