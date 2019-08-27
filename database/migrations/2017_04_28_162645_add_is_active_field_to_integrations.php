<?php

use App\Models\Integration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsActiveFieldToIntegrations extends Migration
{
    public function up()
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->boolean('is_active')->after('internal_api_key');
        });
    }

    public function down()
    {
        Schema::table('integrations', function (Blueprint $table) {
            $table->dropColumn(['is_active']);
        });
    }
}
