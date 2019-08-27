<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsActiveFieldToWarehouseIntegrations extends Migration
{
    public function up()
    {
        Schema::table('warehouse_integrations', function (Blueprint $table) {
            $table->boolean('is_active')->after('internal_api_key');
        });
    }

    public function down()
    {
        Schema::table('warehouse_integrations', function (Blueprint $table) {
            $table->dropColumn(['is_active']);
        });
    }
}
