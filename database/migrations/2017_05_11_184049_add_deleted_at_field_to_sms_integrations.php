<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeletedAtFieldToSmsIntegrations extends Migration
{
    public function up()
    {
        Schema::table('sms_integrations', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('sms_integrations', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
}
