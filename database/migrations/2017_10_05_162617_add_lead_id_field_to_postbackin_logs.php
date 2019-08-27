<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLeadIdFieldToPostbackinLogs extends Migration
{
    public function up()
    {
        Schema::table('postbackin_logs', function (Blueprint $table) {
            $table->dropColumn('integration_id');
            $table->renameColumn('request_ip', 'ip');
            $table->text('request')->change();

            $table->unsignedInteger('lead_id')->index()->after('id');
            $table->char('api_key', 16)->after('lead_id');
            $table->unsignedSmallInteger('response_code')->after('request_ip');
            $table->text('response')->after('response_code');
        });
    }

    public function down()
    {
        //
    }
}
